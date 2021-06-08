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

        $this->init();
    }

    private string $migratePath;
    private string $basePath;
    private int $step;
    private MigrateBuilder $builder;

    private function init(): void
    {
        $options = $this->request->getOptions();

        $this->migratePath = $options['path'] ?? $options['migrate.path'] ?? 'Migration';
        $this->basePath = $this->getAppPath() . '/' . trim($this->migratePath, '/');
        $this->step = (int) ($options['step'] ?? 0);
        $this->builder = new MigrateBuilder($this->db, $this->consoleService);

        if (!file_exists($this->basePath)) {
            File::mkdir($this->basePath);
        }

        $this->createTable();
    }

    public function new(): void
    {
        $this->createFile('migrate/new', $this->generateClassName());
    }

    private string $ddlClassName = 'DDL';

    public function ddl(): void
    {
        $dbService = new DbService($this->db);
        $upSql = '';
        $downSql = '';
        $tables = $dbService->getTables($this->request->getOption('prefix') ?: '');
        foreach ($tables as $tableName) {
            if ($tableName !== $this->tableName) {
                $upSql .= $dbService->getDDL($tableName) . ";\n";
                $downSql .= sprintf('%s$builder->dropTable(\'%s\');%s', str_repeat(' ', 8), $tableName, "\n");
            }
        }

        $this->createFile('migrate/ddl', $this->ddlClassName, compact('upSql', 'downSql'));
    }

    public function up(): void
    {
        $this->migrate('up', $this->request->getOption('all'), function (array $classList): void {
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
        $all = $this->request->getOption('all');

        if ($all) {
            $this->step = 0;
        } else {
            $this->step = (int) ($this->request->getOption('step') ?: 1);
        }

        $this->migrate('down', $all, function (array $classList): void {
            $count = count($classList);
            if ($count === 0) {
                $this->consoleService->writeln('No commits.');
            } else {
                $this->builder->delete($this->tableName, ['version' => array_map('get_class', $classList)]);

                $this->consoleService->writeln(sprintf('Revert count: %d.', $count));
            }
        });
    }

    private function migrate(string $method, bool $all, Closure $success): void
    {
        $files = $this->findClassFiles($all);

        if ($all && $method === 'up') {
            $history = [];
        } else {
            $history = Query::find($this->db)
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
        $transaction = $this->db->beginTransaction();
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

    private function findClassFiles(bool $all = false): array
    {
        $filter = (new PathMatcher())->only('**.php');
        if (!$all) {
            $filter->except('**/' . $this->ddlClassName . '.php');
        }
        return FileHelper::findFiles($this->basePath, [
            'filter' => $filter
        ]);
    }

    private function createFile(string $view, string $className, array $params = []): bool
    {
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

    private function getClassNameByFile(string $file): string
    {
        return sprintf('%s\\%s\\%s', $this->userAppNamespace, $this->migratePath, basename($file, '.php'));
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
