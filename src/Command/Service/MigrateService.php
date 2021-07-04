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

    public function initialize(array $options): void
    {
        parent::initialize($options);

        $this->migratePath = $options['path'] ?? $this->defaultOptions['path'] ?? 'Migration';
        $this->basePath = $this->getAppPath() . '/' . trim($this->migratePath, '/');
        $this->builder = new MigrateBuilder($this->getDb(), $this->consoleService);
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
        $tables = $dbService->getTables($this->options['prefix'] ?? '');
        foreach ($tables as $tableName) {
            if ($tableName !== $this->tableName) {
                $upSql .= $dbService->getDDL($tableName) . ";\n";
                $downSql .= sprintf('%s$builder->dropTable(\'%s\');%s', str_repeat(' ', 8), $tableName, "\n");
            }
        }

        $this->createFile('migrate/init', $this->initClassName, compact('name', 'upSql', 'downSql'));
    }

    public function list(): void
    {
        $this->createTable();

        $list = array_map([$this, 'getClassNameByFile'], $this->findMigrations(true));
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
    private bool $all;

    public function up(): void
    {
        $this->all = $this->options['all'];
        $this->step = (int) ($this->options['step'] ?? 0);

        $this->migrate('up', function (array $instances): bool {
            $count = count($instances);
            $this->consoleService->writeln(sprintf('<comment>%d migration%s to be applied:</>', $count, $count > 1 ? 's' : ''));
            foreach ($instances as $instance) {
                /** @var MigrateInterface $instance */
                $this->consoleService->writeln('- <info>' . $instance::getName() . '</>');
            }
            return $this->consoleService->confirm(sprintf('Apply the above migration%s?', $count > 1 ? 's' : ''), true);
        }, function (array $instances): void {
            $count = count($instances);
            if ($count === 0) {
                $this->consoleService->writeln('Already up to date.');
            } else {
                $rows = [];
                $now = Date::fromUnix();
                foreach ($instances as $instance) {
                    $rows[] = [$this->replaceFromClassName($instance), $now];
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

        $this->migrate('down', function (array $instances): bool {
            $count = count($instances);
            $this->consoleService->writeln(sprintf('<comment>%d migration%s to be reverted:</>', $count, $count > 1 ? 's' : ''));
            foreach ($instances as $instance) {
                /** @var MigrateInterface $instance */
                $this->consoleService->writeln('- <info>' . $instance::getName() . '</>');
            }
            return $this->consoleService->confirm(sprintf('Revert the above migration%s?', $count > 1 ? 's' : ''));
        }, function (array $instances): void {
            $count = count($instances);
            if ($count === 0) {
                $this->consoleService->writeln('No commits.');
            } else {
                $this->builder->delete($this->tableName, ['version' => array_map([$this, 'replaceFromClassName'], $instances)]);

                $this->consoleService->writeln(sprintf('Revert count: %d.', $count));
            }
        });
    }

    private function migrate(string $method, Closure $before, Closure $after): void
    {
        $this->createTable();

        $files = $this->findMigrations($this->all);

        if ($this->all && $method === 'up') {
            $history = [];
        } else {
            $history = $this->getHistory();
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
                $this->error(sprintf("%s::%s() failed, because of %s.", $className, $method, $t->getMessage()));
            }

            try {
                call_user_func($after, $instances);
                $transaction->commit();
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

    private function findMigrations(bool $all = false): array
    {
        $filter = (new PathMatcher())->only('**.php');
        if (!$all) {
            $filter = $filter->except('**/' . $this->initClassName . '.php');
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

        $namespace = $this->userAppNamespace . '\\' . trim(str_replace('/', '\\', $this->migratePath), '/');

        $params['className'] = $className;
        $params['namespace'] = $namespace;

        if (@file_put_contents($this->basePath . '/' . $className . '.php', $this->generateService->render($view, $params))) {
            $this->consoleService->writeln(sprintf('New migration file <info>%s.php</> has been created in <comment>%s</>.', $className, $this->basePath));
            return true;
        } else {
            $this->consoleService->writeln('<error>Generate failed.</>');
            return false;
        }
    }

    private function generateClassName(): string
    {
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

    /**
     * @param string|object $input
     */
    private function replaceFromClassName($input): string
    {
        if (is_object($input)) {
            $input = get_class($input);
        }
        return str_replace('\\', '-', $input);
    }

    private function replaceToClassName(string $className): string
    {
        return str_replace('-', '\\', $className);
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

    /**
     * {@inheritDoc}
     */
    protected function getId(): ?string
    {
        return 'migrate';
    }
}
