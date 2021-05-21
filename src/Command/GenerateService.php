<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Base\Config;
use Ep\Base\View;
use Ep\Helper\File;
use Ep\Helper\Str;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Schema\ColumnSchema;
use Yiisoft\Db\Schema\Schema;
use Yiisoft\Db\Schema\TableSchema;
use Yiisoft\Strings\StringHelper;
use InvalidArgumentException;

final class GenerateService extends Service
{
    private Config $config;
    private Aliases $aliases;
    private View $view;

    public function __construct(Config $config, Aliases $aliases, View $view)
    {
        $this->config = $config;
        $this->aliases = $aliases;
        $this->view = $view->configure([
            'viewPath' => '@ep/views',
            'prefix' => 'generate'
        ]);
    }

    public function render(string $path, array $params): string
    {
        return $this->view->renderPartial($path, $params);
    }

    private string $table;
    private string $path;
    private string $prefix;
    private TableSchema $tableSchema;

    /**
     * @param array $params 可用参数为:
     *
     * - table: 生成目标的表名
     * - path: 生成目标的物理地址，从命名空间后开始计算
     * - prefix: 数据库连接设置外的表前缀
     * 
     * @throws InvalidArgumentException
     */
    public function initModel(array $params): void
    {
        $this->init($params);

        $this->table = $params['table'] ?? '';
        if (!$this->table) {
            $this->required('table');
        }
        $this->path = $params['path'] ?? $params['generate.model.path'] ?? 'Model';
        $this->prefix = $params['prefix'] ?? $params['generate.model.prefix'] ?? '';

        $tableSchema = $this->db->getTableSchema($this->table);
        if (!$tableSchema) {
            $this->invalid('table', $this->table);
        }
        $this->tableSchema = $tableSchema;
    }

    public function createModel(): string
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            File::mkdir($filePath);
        }
        if (@file_put_contents($this->getModelFileName(), $this->render('model', [
            'namespace' => $this->getNamespace(),
            'primaryKey' => $this->getPrimaryKey(),
            'tableName' => $this->getTableName(),
            'className' => $this->getModelClassName(),
            'property' => $this->getModelProperty(),
            'rules' => $this->getModelRules()
        ]))) {
            return sprintf('The file "%s.php" has been created in "%s".', $this->getModelClassName(), $filePath);
        } else {
            return 'Generate failed.';
        }
    }

    public function updateModel(): string
    {
        $filename = $this->getModelFileName();
        $rules = [
            '~(/\*\*\s).+( \*/\sclass)~s' => '$1' . $this->getModelProperty() . '$2',
            '~(public const PK = ).+(;)~' => '$1' . $this->getPrimaryKey() . '$2',
        ];
        $content = preg_replace(array_keys($rules), array_values($rules), file_get_contents($filename));
        if (@file_put_contents($filename, $content)) {
            return sprintf('%s.php has been overrided in %s', $this->getModelClassName(), $this->getFilePath());
        } else {
            return 'Overwrite model failed.';
        }
    }

    public function hasModel(): bool
    {
        return file_exists($this->getModelFileName());
    }

    private ?string $appPath = null;

    /**
     * @throws InvalidArgumentException
     */
    public function getAppPath(): string
    {
        if ($this->appPath === null) {
            $vendorDirname = dirname($this->aliases->get($this->config->vendorPath));
            $composerPath = $vendorDirname . '/composer.json';
            if (!file_exists($composerPath)) {
                throw new InvalidArgumentException('Unable to find composer.json in your project root.');
            }
            $composerContent = json_decode(file_get_contents($composerPath), true);
            $autoload = ($composerContent['autoload']['psr-4'] ?? []) + ($composerContent['autoload-dev']['psr-4'] ?? []);
            $appPath = null;
            foreach ($autoload as $ns => $path) {
                if ($ns === $this->appNamespace . '\\') {
                    $appPath = $path;
                    break;
                }
            }
            if ($appPath === null) {
                throw new InvalidArgumentException('You should set the "autoload[psr-4]" configuration in your composer.json first.');
            }
            $this->appPath = $vendorDirname . '/' . $appPath;
        }

        return $this->appPath;
    }

    private function getNamespace(): string
    {
        return sprintf('%s\\%s', $this->appNamespace, implode('\\', array_map([Str::class, 'toPascalCase'], explode('/', $this->path))));
    }

    private function getPrimaryKey(): string
    {
        $primaryKeys = $this->tableSchema->getPrimaryKey();
        switch (count($primaryKeys)) {
            case 0:
                return "''";
            case 1:
                return "'{$primaryKeys[0]}'";
            default:
                return sprintf("['%s']", implode("', '", $primaryKeys));
        }
    }

    private function getTableName(): string
    {
        return substr($this->table, strlen($this->db->getTablePrefix()));
    }

    private function getModelClassName(): string
    {
        return Str::toPascalCase(substr($this->getTableName(), strlen($this->prefix)));
    }

    private function getModelProperty(): string
    {
        $property = '';
        foreach ($this->getColumns() as $field => $column) {
            $property .= ' * @property ' . $this->typecast($column->getPhpType()) . ' $' . $field . ($column->getComment() ? ' ' . $column->getComment() : '') . "\n";
        }
        return $property;
    }

    /**
     * @return string[]
     */
    private function getModelRules(): array
    {
        $fields = [];
        $types = [];
        $ruleNs = 'Yiisoft\Validator\Rule\\';
        foreach ($this->getColumns() as $field => $column) {
            if ($column->isPrimaryKey()) {
                continue;
            }
            switch ($column->getType()) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $fields[$field][] = 'Number:integer()';
                    $types['Number'] = true;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $fields[$field][] = 'Boolean';
                    $types['Boolean'] = true;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $fields[$field][] = 'Number';
                    $types['Number'] = true;
                    break;
                case Schema::TYPE_CHAR:
                case Schema::TYPE_STRING:
                    if ($column->getSize() === null) {
                        break;
                    }
                    $fields[$field][] = 'HasLength:max(' . $column->getSize() . ')';
                    $types['HasLength'] = true;
                    break;
            }
            if (StringHelper::endsWith($field, 'email')) {
                $fields[$field][] = 'Email';
                $types['Email'] = true;
            }
            if ($column->isAllowNull()) {
                if (isset($fields[$field])) {
                    $fields[$field] = array_map(fn ($rule): string => $rule . ':skipOnEmpty(true)', $fields[$field]);
                }
            } else {
                $fields[$field][] = 'Required';
                $types['Required'] = true;
            }
        }
        switch (count($types)) {
            case 0:
                $use = '';
                break;
            case 1:
                $use = 'use ' . $ruleNs . key($types) . ";\n";
                break;
            default:
                $use = 'use ' . $ruleNs . "{\n";
                foreach ($types as $type => $v) {
                    $use .= "    {$type},\n";
                }
                $use .= "};\n";
                break;
        }
        $rules = [];
        foreach ($fields as $field => $items) {
            $rules[$field] = [];
            foreach ($items as $rule) {
                $array = explode(':', $rule);
                $string = sprintf('(new %s())', array_shift($array));
                foreach ($array as $method) {
                    $string .= '->' . $method;
                }
                $rules[$field][] = $string;
            }
        }
        return [$use, $rules];
    }

    private function getFilePath(): string
    {
        return sprintf('%s/%s', $this->getAppPath(), $this->path);
    }

    private function getModelFileName(): string
    {
        return $this->getFilePath() . '/' . $this->getModelClassName() . '.php';
    }

    /**
     * @return ColumnSchema[]
     */
    private function getColumns(): array
    {
        return $this->tableSchema->getColumns();
    }

    private function typecast(string $type): string
    {
        switch ($type) {
            case 'integer':
                return 'int';
            case 'boolean':
                return 'bool';
            default:
                return $type;
        }
    }
}
