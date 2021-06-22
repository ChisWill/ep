<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;
use Ep\Db\ActiveRecord;
use Ep\Db\Query;
use Ep\Db\Service as DbService;
use Ep\Helper\Date;
use Ep\Helper\File;
use Psr\Container\ContainerInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use Closure;
use Throwable;

final class MigrateService extends Service
{
    private GenerateService $generateService;
    private string $tableName;

    public function __construct(
        ContainerInterface $container,
        GenerateService $generateService
    ) {
        parent::__construct($container);

        $this->generateService = $generateService;
        $this->tableName = $this->config->migrationTableName;
    }

    private string $migratePath;
    private string $basePath;
    private MigrateBuilder $builder;

    public function init(array $options): void
    {
        parent::init($options);

        $this->migratePath = $options['path'] ?? $options['migrate.path'] ?? 'Migration';
        $this->basePath = $this->getAppPath() . '/' . trim($this->migratePath, '/');
        $this->builder = new MigrateBuilder($this->getDb(), $this->consoleService);
    }

    public function new(): void
    {
        $this->createFile('migrate/new', $this->generateClassName());
    }

    private string $ddlClassName = 'DDL';

    public function ddl(): void
    {
        $this->createTable();

        $dbService = new DbService($this->getDb());
        $upSql = '';
        $downSql = '';
        $tables = $dbService->getTables($this->options['prefix'] ?? '');
        foreach ($tables as $tableName) {
            if ($tableName !== $this->tableName) {
                $upSql .= $dbService->getDDL($tableName) . ";\n";
                $downSql .= sprintf('%s$builder->dropTable(\'%s\');%s', str_repeat(' ', 8), $tableName, "\n");
            }
        }

        $this->createFile('migrate/ddl', $this->ddlClassName, compact('upSql', 'downSql'));
    }

    private int $step;
    private bool $all;

    public function up(): void
    {
        $this->all = $this->options['all'];
        $this->step = (int) ($this->options['step'] ?? 0);

        $this->migrate('up', function (array $classList): void {
            $count = count($classList);
            if ($count === 0) {
                $this->consoleService->writeln('Already up to date.');
            } else {
                $rows = [];
                $now = Date::fromUnix();
                foreach ($classList as $class) {
                    $rows[] = [get_class($class), $now];
                }

                $this->builder->batchInsert($this->tableName, ['version', ActiveRecord::CREATED_AT], $rows);

                $this->consoleService->writeln(sprintf('Commit count: %d.', $count));
            }
        });
    }

    public function down(): void
    {
        $this->all = $this->options['all'];
        $this->step = $this->all ? 0 : (int) ($this->options['step'] ?? 1);

        $this->migrate('down', function (array $classList): void {
            $count = count($classList);
            if ($count === 0) {
                $this->consoleService->writeln('No commits.');
            } else {
                $this->builder->delete($this->tableName, ['version' => array_map('get_class', $classList)]);

                $this->consoleService->writeln(sprintf('Revert count: %d.', $count));
            }
        });
    }

    private function migrate(string $method, Closure $success): void
    {
        $this->createTable();

        $files = $this->findMigrations($this->all);

        if ($this->all && $method === 'up') {
            $history = [];
        } else {
            $history = Query::find($this->getDb())
                ->select('version')
                ->from($this->tableName)
                ->column();
        }

        switch ($method) {
            case 'up':
                sort($files);
                $reverse = false;
                break;
            case 'down':
                rsort($files);
                $reverse = true;
                break;
            default:
                $this->throw('Unsupport migrate method.');
        }

        $classList = [];
        $transaction = $this->getDb()->beginTransaction();
        $count = 0;
        foreach ($files as $file) {
            if ($this->step > 0 && $count === $this->step) {
                break;
            }
            $className = $this->getClassNameByFile($file);
            if (!class_exists($className)) {
                $this->throw("The class \"{$className}\" is not exists.");
            }
            $skip = in_array($className, $history);
            if ($reverse) {
                $skip = !$skip;
            }
            if ($skip) {
                continue;
            }
            $class = new $className();
            if (!$class instanceof MigrateInterface) {
                $this->throw("The class \"{$className}\" is not implements \"" . MigrateInterface::class . "\".");
            }
            try {
                call_user_func([$class, $method], $this->builder);
                array_unshift($classList, $class);
                $count++;
            } catch (Throwable $t) {
                $transaction->rollBack();
                $this->throw(sprintf("%s::%s() failed.", $className, $method));
            }
        }

        try {
            call_user_func($success, $classList);
            $transaction->commit();
        } catch (Throwable $t) {
            $transaction->rollBack();
            $this->throw($t->getMessage());
        }
    }

    private function findMigrations(bool $all = false): array
    {
        $filter = (new PathMatcher())->only('**.php');
        if (!$all) {
            $filter = $filter->except('**/' . $this->ddlClassName . '.php');
        }
        return FileHelper::findFiles($this->basePath, [
            'filter' => $filter
        ]);
    }

    private function createFile(string $view, string $className, array $params = []): bool
    {
        if (!file_exists($this->basePath)) {
            File::mkdir($this->basePath);
        }

        $namespace = $this->userAppNamespace . '\\' . $this->migratePath;

        $params['className'] = $className;
        $params['namespace'] = $namespace;

        if (@file_put_contents($this->basePath . '/' . $className . '.php', $this->generateService->render($view, $params))) {
            $this->consoleService->writeln(sprintf('The file "%s.php" has been created in "%s".', $className, $this->basePath));
            return true;
        } else {
            $this->consoleService->writeln('Generate failed.');
            return false;
        }
    }

    private function generateClassName(): string
    {
        $baseClassName = sprintf('Migration_%s_', date('Ymd'));
        $files = FileHelper::findFiles($this->basePath, [
            'filter' => (new PathMatcher())->only("**{$baseClassName}*.php")
        ]);

        if ($files) {
            rsort($files);
            $suffix = (int) str_replace($baseClassName, '', basename($files[0], '.php')) + 1;
        } else {
            $suffix = 1;
        }

        return $baseClassName . $suffix;
    }

    private function createTable(): void
    {
        try {
            $this->builder->find()->from($this->tableName)->one();
        } catch (Exception $t) {
            $this->builder->createTable($this->tableName, [
                'id' => $this->builder->primaryKey(),
                'version' => $this->builder->string(100)->notNull(),
                ActiveRecord::CREATED_AT => $this->builder->dateTime()->notNull()
            ]);
        }
    }
}
