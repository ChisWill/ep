<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep;
use Ep\Helper\File;
use Ep\Helper\Str;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Schema\ColumnSchema;
use Yiisoft\Db\Schema\Schema;
use Yiisoft\Db\Schema\TableSchema;
use Yiisoft\Strings\StringHelper;
use Psr\Container\NotFoundExceptionInterface;
use Closure;
use InvalidArgumentException;

final class GenerateService
{
    private string $autoloadPath;
    private string $appNamespace;
    private string $appPath;
    private string $table;
    private string $prefix;
    private Connection $db;
    private ?TableSchema $schema;

    /**
     * @throws InvalidArgumentException
     */
    public function validateModel($params)
    {
        $this->autoloadPath = $params['autoloadPath'];
        $this->appNamespace = $params['appNamespace'];
        $this->appPath = $this->getAppPath();
        $this->table = $params['table'] ?? '';
        if (!$this->table) {
            $this->required('table');
        }
        $this->prefix = $params['prefix'] ?? $params['generate.model.prefix'] ?? 'Model';
        try {
            $db = $params['db'] ?? $params['generate.model.db'] ?? $params['common.db'] ?? '';
            $this->db = $this->getDb($db ?: null);
        } catch (NotFoundExceptionInterface $e) {
            $this->invalid('db', $db);
        }
        $this->schema = $this->db->getTableSchema($this->table);
        if (!$this->schema) {
            $this->invalid('table', $this->table);
        }
    }

    public function getNamespace(): string
    {
        return sprintf('%s\\%s', $this->appNamespace, implode('\\', array_map([Str::class, 'toPascalCase'], explode('/', $this->prefix))));
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
        return preg_replace('/' . $this->db->getTablePrefix() . '/', '', $this->table, 1);
    }

    public function getClassName(): string
    {
        return Str::toPascalCase($this->getTableName());
    }

    /**
     * @return ColumnSchema[]
     */
    public function getColumns(): array
    {
        return $this->schema->getColumns();
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
                    $fields[$field] = array_map(fn ($rule) => $rule . ':skipOnEmpty(true)', $fields[$field]);
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

    public function typecast(): Closure
    {
        return function (string $type): string {
            switch ($type) {
                case 'integer':
                    return 'int';
                case 'boolean':
                    return 'bool';
                default:
                    return $type;
            }
        };
    }

    public function createModel(string $content): string
    {
        $filePath = sprintf('%s/%s/%s', dirname($this->autoloadPath, 2), $this->appPath, $this->prefix);
        if (!file_exists($filePath)) {
            File::mkdir($filePath);
        }
        if (@file_put_contents($filePath . '/' . $this->getClassName() . '.php', $content)) {
            return sprintf('%s.php has been created in %s', $this->getClassName(), $filePath);
        } else {
            return 'Generate model failed.';
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getAppPath(): string
    {
        $composerPath = dirname($this->autoloadPath, 2) . '/composer.json';
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
        return $appPath;
    }

    /**
     * @throws NotFoundExceptionInterface
     */
    private function getDb(?string $id = null): Connection
    {
        return Ep::getDb($id);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function required(string $option)
    {
        throw new InvalidArgumentException("The \"{$option}\" option is required.");
    }

    /**
     * @throws InvalidArgumentException
     */
    private function invalid(string $option, string $value)
    {
        throw new InvalidArgumentException("The value \"{$value}\" of the option \"{$option}\" is invalid.");
    }
}
