<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Base\View;
use Ep\Helper\File;
use Ep\Helper\Str;
use Ep\Kit\Crypt;
use Yiisoft\Db\Schema\ColumnSchema;
use Yiisoft\Db\Schema\Schema;
use Yiisoft\Db\Schema\TableSchema;
use Yiisoft\Strings\StringHelper;
use Psr\Container\ContainerInterface;
use InvalidArgumentException;

final class GenerateService extends Service
{
    private View $view;
    private Crypt $crypt;

    public function __construct(
        ContainerInterface $container,
        View $view,
        Crypt $crypt
    ) {
        parent::__construct($container);

        $this->view = $view
            ->withViewPath('@ep/views')
            ->withPrefix('generate');
        $this->crypt = $crypt;
    }

    public function render(string $path, array $params): string
    {
        return $this->view->renderPartial($path, $params);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        switch ($this->request->getRoute()) {
            case 'generate/model':
                $this->configureModel();
                break;
        }
    }

    private string $table;
    private string $path;
    private string $prefix;
    private TableSchema $tableSchema;

    private function configureModel(): void
    {
        $this->table = $this->request->getOption('table');
        $this->path = $this->request->getOption('path') ?? $this->defaultOptions['model.path'] ?? 'Model';
        $this->prefix = $this->request->getOption('prefix') ?? $this->defaultOptions['model.prefix'] ?? '';

        $tableSchema = $this->getDb()->getTableSchema($this->table, true);
        if (!$tableSchema) {
            $this->error(sprintf('The table "%s" is not exists.', $this->table));
        }
        $this->tableSchema = $tableSchema;
    }

    public function createKey(): void
    {
        $this->setEnvFile(base64_encode($this->crypt->generateKey()));

        $this->consoleService->writeln('<info>Generate secret key successfully.</>');
    }

    public function createModel(): void
    {
        $filePath = $this->getFilePath();
        if (!file_exists($filePath)) {
            File::mkdir($filePath);
        }
        [$classes, $rules] = $this->getModelRuleData();
        if (@file_put_contents($this->getModelFileName(), $this->render('model', [
            'namespace' => $this->getNamespace(),
            'primaryKey' => $this->getPrimaryKey(),
            'tableName' => $this->getTableName(),
            'className' => $this->getModelClassName(),
            'property' => $this->getModelProperty(),
            'use' => $this->getUseStatement($classes),
            'rules' => $this->getModelRules($rules)
        ]))) {
            $this->consoleService->writeln(sprintf('The model file <info>%s.php</> has been created in <comment>%s</>', $this->getModelClassName(), $filePath));
        } else {
            $this->consoleService->writeln('<error>Generate failed.</>');
        }
    }

    public function updateModel(): void
    {
        $filename = $this->getModelFileName();
        [$classes, $rules] = $this->getModelRuleData();
        [$useRule, $useStatement] = $this->getUseReplacement($filename, $classes);

        $replace = [
            $useRule => $useStatement,
            '~(/\*\*\s).+( \*/\sclass)~Us' => '$1' . $this->getModelProperty() . '$2',
            '~(const PK = ).+(;)~U' => '$1' . $this->getPrimaryKey() . '$2',
            '~(function rules\(\): array\s+\{\s+)return.*;\s(\s+\})~Us' => '$1' . $this->getModelRules($rules) . '$2'
        ];
        if (@file_put_contents($filename, preg_replace(array_keys($replace), array_values($replace), file_get_contents($filename), 1))) {
            $this->consoleService->writeln(sprintf('The model file <info>%s.php</> has been <fg=magenta>overrided</> in <comment>%s</>', $this->getModelClassName(), $this->getFilePath()));
        } else {
            $this->consoleService->writeln('<error>Overwrite model failed.</>');
        }
    }

    public function hasModel(): bool
    {
        return file_exists($this->getModelFileName());
    }

    private function setEnvFile(string $key): void
    {
        $file = $this->util->rootPath('.env');
        if (!file_exists($file)) {
            throw new InvalidArgumentException('The environment file ".env" is not exists.');
        }

        $count = 0;
        $data = preg_replace(
            '/^SECRET_KEY=.*/m',
            'SECRET_KEY=' . $key,
            file_get_contents($file),
            1,
            $count
        );
        if ($count === 0) {
            throw new InvalidArgumentException('The configure "SECRET_KEY" is not exists.');
        }

        file_put_contents($file, $data);
    }

    private function getNamespace(): string
    {
        return sprintf('%s\\%s', $this->userRootNamespace, implode('\\', array_map([Str::class, 'toPascalCase'], explode('/', $this->path))));
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
        return substr($this->table, strlen($this->getDb()->getTablePrefix()));
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

    private function getModelRuleData(): array
    {
        $classes = [];
        $fields = [];
        foreach ($this->getColumns() as $field => $column) {
            if ($column->isPrimaryKey()) {
                continue;
            }
            if (!$column->isAllowNull()) {
                $fields[$field][] = 'Required';
                $classes[] = 'Required';
            }
            switch ($column->getType()) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $fields[$field][] = 'Number:integer()';
                    $classes[] = 'Number';
                    break;
                case Schema::TYPE_BOOLEAN:
                    $fields[$field][] = 'Boolean';
                    $classes[] = 'Boolean';
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $fields[$field][] = 'Number';
                    $classes[] = 'Number';
                    break;
                case Schema::TYPE_CHAR:
                case Schema::TYPE_STRING:
                    if ($column->getSize() === null) {
                        break;
                    }
                    $fields[$field][] = 'HasLength:max(' . $column->getSize() . ')';
                    $classes[] = 'HasLength';
                    if (StringHelper::endsWith($field, 'email')) {
                        $fields[$field][] = 'Email';
                        $classes[] = 'Email';
                    }
                    break;
            }
            if ($column->isAllowNull() && isset($fields[$field])) {
                $fields[$field] = array_map(fn ($rule): string => $rule . ':skipOnEmpty(true)', $fields[$field]);
            }
        }

        $classes = array_unique($classes);
        sort($classes);

        $rules = [];
        foreach ($fields as $field => $items) {
            $rules[$field] = [];
            foreach ($items as $rule) {
                $array = explode(':', $rule);
                $string = sprintf('%s::rule()', array_shift($array));
                foreach ($array as $method) {
                    $string .= '->' . $method;
                }
                $rules[$field][] = $string;
            }
        }

        return [$classes, $rules];
    }

    private function getUseReplacement(string $filename, array $classes): array
    {
        $useRule = '~use\s+Yiisoft\\\Validator\\\Rule\\\([\s\S]+);~U';
        if (preg_match($useRule, file_get_contents($filename), $matches)) {
            $classes = array_unique(array_merge($classes, array_map(
                static fn (string $row): string => trim($row, ', '),
                array_filter(
                    explode("\n", $matches[1]),
                    static fn (string $row): int => preg_match('~\w+~', $row)
                )
            )));
            sort($classes);
            $useStatement = $this->getUseStatement($classes, false);
        } else {
            $useRule = '~(use\s+[\w\\\]+\\\ActiveRecord[\s\w]*;)~U';
            $useStatement = ($classes ? "$1\n" : '$1') . $this->getUseStatement($classes, false);
        }

        return [$useRule, $useStatement];
    }

    private function getUseStatement(array $classes, bool $isCreate = true): string
    {
        $base = $isCreate ? "\nuse Ep\Db\ActiveRecord;\n" : '';
        $ruleNs = 'Yiisoft\Validator\Rule\\';
        switch (count($classes)) {
            case 0:
                $use = '';
                break;
            case 1:
                $use = 'use ' . $ruleNs . current($classes) . ";";
                break;
            default:
                $use = 'use ' . $ruleNs . "{\n";
                foreach ($classes as $class) {
                    $use .= "    {$class},\n";
                }
                $use .= "};";
                break;
        }
        if ($isCreate && $use) {
            $use .= "\n";
        }
        return $base . $use;
    }

    private function getModelRules(array $rules): string
    {
        $string = 'return $this->userRules() + ';
        if ($rules) {
            $string .= "[\n";
            foreach ($rules as $field => $items) {
                $string .= sprintf("%s'%s' => [\n", str_repeat(' ', 12), $field);
                foreach ($items as $rule) {
                    $string .= str_repeat(' ', 16) . $rule . ",\n";
                }
                $string .= str_repeat(' ', 12) . "],\n";
            }
            $string .= str_repeat(' ', 8) . '];';
        } else {
            $string .= '[];';
        }
        return $string . "\n";
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
