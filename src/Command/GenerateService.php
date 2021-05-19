<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep;
use Ep\Base\Config;
use Ep\Helper\File;
use Ep\Helper\Str;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Schema\ColumnSchema;
use Yiisoft\Db\Schema\Schema;
use Yiisoft\Db\Schema\TableSchema;
use Yiisoft\Factory\Exceptions\NotFoundException;
use Yiisoft\Strings\StringHelper;
use InvalidArgumentException;

final class GenerateService extends Service
{
    private Config $config;
    private Aliases $aliases;

    public function __construct(Config $config, Aliases $aliases)
    {
        $this->config = $config;
        $this->aliases = $aliases;
    }

    private string $appNamespace;
    private string $table;
    private string $path;
    private string $prefix;
    private Connection $db;
    private ?TableSchema $schema;

    /**
     * @throws InvalidArgumentException
     */
    public function validateModel(array $params)
    {
        $this->appNamespace = $params['common.appNamespace'];
        $this->table = $params['table'] ?? '';
        if (!$this->table) {
            $this->required('table');
        }
        $this->path = $params['path'] ?? $params['generate.model.path'] ?? 'Model';
        $this->prefix = $params['prefix'] ?? $params['generate.model.prefix'] ?? '';
        $db = $params['db'] ?? $params['generate.model.db'] ?? $params['common.db'] ?? null;
        try {
            $this->db = Ep::getDb($db);
        } catch (NotFoundException $e) {
            $this->invalid('db', $db);
        }
        $this->schema = $this->db->getTableSchema($this->table);
        if (!$this->schema) {
            $this->invalid('table', $this->table);
        }
    }

    public function getNamespace(): string
    {
        return sprintf('%s\\%s', $this->appNamespace, implode('\\', array_map([Str::class, 'toPascalCase'], explode('/', $this->path))));
    }

    public function getPrimaryKey(): string
    {
        $primaryKeys = $this->schema->getPrimaryKey();
        switch (count($primaryKeys)) {
            case 0:
                return "''";
            case 1:
                return "'{$primaryKeys[0]}'";
            default:
                return sprintf("['%s']", implode("', '", $primaryKeys));
        }
    }

    public function getTableName(): string
    {
        return substr($this->table, strlen($this->db->getTablePrefix()));
    }

    public function getClassName(): string
    {
        return Str::toPascalCase(substr($this->getTableName(), strlen($this->prefix)));
    }

    public function getProperty(): string
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
    public function getRules(): array
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

    public function hasModel(): bool
    {
        return file_exists($this->getFileName());
    }

    public function createModel(string $content): string
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            File::mkdir($filePath);
        }
        if (@file_put_contents($this->getFileName(), $content)) {
            return sprintf('%s.php has been created in %s', $this->getClassName(), $filePath);
        } else {
            return 'Generate model failed.';
        }
    }

    public function updateModel(): string
    {
        $filename = $this->getFileName();
        $rules = [
            '~(/\*\*\s).+( \*/\sclass)~s' => '$1' . $this->getProperty() . '$2',
            '~(public const PK = ).+(;)~' => '$1' . $this->getPrimaryKey() . '$2',
        ];
        $content = preg_replace(array_keys($rules), array_values($rules), file_get_contents($filename));
        if (@file_put_contents($filename, $content)) {
            return sprintf('%s.php has been overrided in %s', $this->getClassName(), $this->getFilePath());
        } else {
            return 'Overwrite model failed.';
        }
    }

    public function isMultiple(string $param): bool
    {
        return strpos($param, ',') !== false;
    }

    public function getPieces(string $param): array
    {
        return explode(',', $param);
    }

    private function getFilePath(): string
    {
        return sprintf('%s/%s', $this->getAppPath(), $this->path);
    }

    private function getFileName(): string
    {
        return $this->getFilePath() . '/' . $this->getClassName() . '.php';
    }

    /**
     * @return ColumnSchema[]
     */
    private function getColumns(): array
    {
        return $this->schema->getColumns();
    }

    private ?string $appPath = null;

    /**
     * @throws InvalidArgumentException
     */
    private function getAppPath(): string
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
