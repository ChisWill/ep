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
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use Psr\Container\ContainerInterface;
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

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->migratePath = $this->request->getOption('path') ?? $this->defaultOptions['path'] ?? 'Migration';
        $this->basePath = $this->getAppPath() . '/' . trim($this->migratePath, '/');

        if ($this->request->hasOption('db')) {
            $this->builder = new MigrateBuilder($this->getDb(), $this->consoleService);
        }
    }

    public function create(string $name): void
    {
        $this->createFile('migrate/default', $this->generateClassName(), compact('name'));
    }

    private string $initClassName = 'Initialization';

    public function init(): void
    {
        $this->createTable();

        $dbService = new DbService($this->getDb());
        $name = $this->initClassName;
        $upSql = '';
        $downSql = '';
        $tables = $dbService->getTables($this->request->getOption('prefix') ?? '');
        foreach ($tables as $tableName) {
            if ($tableName !== $this->tableName) {
                $upSql .= $dbService->getDDL($tableName) . ";\n";
                $downSql .= sprintf('%s$builder->dropTable(\'%s\');%s', str_repeat(' ', 8), $tableName, "\n");
            }
        }
        $insertData = [];
        if ($this->request->getOption('data')) {
            foreach ($tables as $tableName) {
                $data = Query::find($this->getDb())->from($tableName)->all();
                if (!$data) {
                    continue;
                }
                $insertData[$tableName] = [
                    'columns' => array_keys($data[0]),
                    'rows' => $data
                ];
            }
        }

        $this->createFile('migrate/init', $this->initClassName, compact('name', 'upSql', 'downSql', 'insertData'));
    }

    public function list(): void
    {
        $this->createTable();

        $list = array_map([$this, 'getClassNameByFile'], $this->findMigrations());
        $history = $this->getHistory();

        $total = count($list);
        $this->consoleService->writeln(sprintf('Total <info>%d</> migration%s found in <comment>%s</>', $total, $total > 1 ? 's' : '', $this->basePath));

        foreach ($list as $class) {
            if (in_array($class, $history)) {
                $status = 'executed';
                $color = 'yellow';
            } else {
                $status = 'pending';
                $color = 'magenta';
            }
            /** @var MigrateInterface $class */
            $this->consoleService->writeln(sprintf('- <info>%s</> [<fg=%s>%s</>]', $class::getName(), $color, $status));
        }
    }

    private int $step;

    public function up(): void
    {
        $this->step = (int) ($this->request->getOption('step') ?? 0);

        $this->migrate('up', function (array $instances): bool {
            if (!$instances) {
                $this->consoleService->writeln('Already up to date.');
                return false;
            }
            $count = count($instances);
            $this->consoleService->writeln(sprintf('<comment>%d migration%s to be applied:</>', $count, $count > 1 ? 's' : ''));
            foreach ($instances as $instance) {
                /** @var MigrateInterface $instance */
                $this->consoleService->writeln('- <info>' . $instance::getName() . '</>');
            }
            return $this->consoleService->confirm(sprintf('Apply the above migration%s?', $count > 1 ? 's' : ''), true);
        }, function (array $instances): void {
            $this->builder->batchInsert(
                $this->tableName,
                ['version', ActiveRecord::CREATED_AT],
                array_map(fn ($instance): array => [$this->replaceFromClassName(get_class($instance)), Date::fromUnix()], $instances)
            );

            $this->consoleService->writeln(sprintf('Commit count: %d.', count($instances)));
        });
    }

    public function down(): void
    {
        $this->step = $this->request->getOption('all') ? 0 : (int) ($this->request->getOption('step') ?? 1);

        $this->migrate('down', function (array $instances): bool {
            if (!$instances) {
                $this->consoleService->writeln('No commits.');
                return false;
            }
            $count = count($instances);
            $this->consoleService->writeln(sprintf('<comment>%d migration%s to be reverted:</>', $count, $count > 1 ? 's' : ''));
            foreach ($instances as $instance) {
                /** @var MigrateInterface $instance */
                $this->consoleService->writeln('- <info>' . $instance::getName() . '</>');
            }
            return $this->consoleService->confirm(sprintf('Revert the above migration%s?', $count > 1 ? 's' : ''));
        }, function (array $instances): void {
            $this->builder->delete(
                $this->tableName,
                ['version' => array_map(fn ($instance): string => $this->replaceFromClassName(get_class($instance)), $instances)]
            );

            $this->consoleService->writeln(sprintf('Revert count: %d.', count($instances)));
        });
    }

    private function migrate(string $method, Closure $before, Closure $after): void
    {
        $this->createTable();

        $files = $this->findMigrations();

        $history = $this->getHistory();

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
                $this->error('Unsupport migrate method.');
        }

        $instances = [];
        $count = 0;
        foreach ($files as $file) {
            if ($this->step > 0 && $count === $this->step) {
                break;
            }
            $className = $this->getClassNameByFile($file);
            if (!class_exists($className)) {
                $this->error("The class {$className} is not exists.");
            }
            $skip = in_array($className, $history);
            if ($reverse) {
                $skip = !$skip;
            }
            if ($skip) {
                continue;
            }
            $instance = new $className();
            if (!$instance instanceof MigrateInterface) {
                $this->error("The class {$className} is not implements " . MigrateInterface::class . ".");
            }
            $instances[] = $instance;
            $count++;
        }

        if (call_user_func($before, $instances)) {
            $transaction = $this->getDb()->beginTransaction();
            try {
                foreach ($instances as $instance) {
                    call_user_func([$instance, $method], $this->builder);
                }
            } catch (Throwable $t) {
                $transaction->rollBack();
                $this->error(sprintf("%s::%s() failed, because %s.", $className, $method, $t->getMessage()));
            }

            try {
                call_user_func($after, $instances);
                if ($this->getDb()->getPDO()->inTransaction()) {
                    $transaction->commit();
                }
            } catch (Throwable $t) {
                $transaction->rollBack();
                $this->error($t->getMessage());
            }
        }
    }

    private function getHistory(): array
    {
        return array_map(
            [$this, 'replaceToClassName'],
            Query::find($this->getDb())
                ->select('version')
                ->from($this->tableName)
                ->where(['LIKE', 'version', $this->replaceFromClassName($this->getClassNameByFile($this->basePath)) . '%', false])
                ->column()
        );
    }

    private function findMigrations(): array
    {
        return FileHelper::findFiles($this->basePath, [
            'filter' => (new PathMatcher())->only('**.php')
        ]);
    }

    private function createFile(string $view, string $className, array $params = []): bool
    {
        $this->createDir();

        $namespace = $this->userRootNamespace . '\\' . trim(str_replace('/', '\\', $this->migratePath), '/');

        $params['className'] = $className;
        $params['namespace'] = $namespace;

        if (@file_put_contents(sprintf('%s/%s.php', $this->basePath, $className), $this->generateService->render($view, $params))) {
            $this->consoleService->writeln(sprintf('New migration file <info>%s.php</> has been created in <comment>%s</>', $className, $this->basePath));
            return true;
        } else {
            $this->consoleService->writeln('<error>Generate failed.</>');
            return false;
        }
    }

    private function generateClassName(): string
    {
        $this->createDir();

        $baseClassName = sprintf('M%s_', date('Ymd'));
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

    private function createDir(): void
    {
        if (!file_exists($this->basePath)) {
            File::mkdir($this->basePath);
        }
    }

    private function replaceFromClassName(string $className): string
    {
        return str_replace('\\', '-', $className);
    }

    private function replaceToClassName(string $input): string
    {
        return str_replace('-', '\\', $input);
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
            $this->builder->createIndex('idx_version', $this->tableName, 'version');
        }
    }
}
