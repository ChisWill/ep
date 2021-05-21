<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Base\Config;
use Ep\Console\Service as ConsoleService;
use Ep\Contract\MigrateInterface;
use Ep\Db\ActiveRecord;
use Ep\Db\Query;
use Ep\Db\Service as DbService;
use Ep\Helper\Date;
use Ep\Helper\File;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use InvalidArgumentException;
use Throwable;

final class MigrateService extends Service
{
    private string $tableName;
    private GenerateService $generateService;
    private ConsoleService $consoleService;

    public function __construct(
        Config $config,
        GenerateService $generateService,
        ConsoleService $consoleService
    ) {
        $this->tableName = $config->migrationTableName;
        $this->generateService = $generateService;
        $this->consoleService = $consoleService;
    }

    private string $basePath;
    private string $migratePath;
    private MigrateBuilder $builder;

    /**
     * @throws InvalidArgumentException
     */
    public function init(array $params): void
    {
        parent::init($params);

        $this->generateService->init($params);

        $this->migratePath = $params['path'] ?? $params['migrate.path'] ?? null;
        if (!$this->migratePath) {
            $this->required('path');
        }
        $this->basePath = $this->generateService->getAppPath() . '/' . $this->migratePath;

        $this->builder = new MigrateBuilder($this->db);

        if (!file_exists($this->basePath)) {
            File::mkdir($this->basePath);
        }

        $this->createTable();
    }

    public function new(): string
    {
        return $this->createFile('migrate/new', $this->generateClassName());
    }

    private string $prefix;

    /**
     * @param array $params 可用参数为:
     *
     * - prefix: 需要过滤的数据库表前缀
     */
    public function initDDL(array $params): void
    {
        $this->init($params);

        $this->prefix = $params['prefix'] ?? '';
    }

    private string $ddlClassName = 'DDL';

    public function ddl(): string
    {
        $className = $this->ddlClassName;

        $dbService = new DbService($this->db);
        $ddl = '';
        foreach ($dbService->getTables($this->prefix) as $tableName) {
            $ddl .= $dbService->getDDL($tableName) . ";\n";
        }

        return $this->createFile('migrate/ddl', $className, compact('ddl'));
    }

    public function all(): string
    {
        $answer = $this->consoleService->prompt('Are you sure migrate all records? [Yes|No]');
        if ($answer === 'Yes') {
            return $this->migrate();
        } else {
            return 'Skipped.';
        }
    }

    public function up(): string
    {
        $history = Query::find($this->db)
            ->select('version')
            ->from($this->tableName)
            ->column();

        return $this->migrate($history);
    }

    private function migrate(array $history = []): string
    {
        $files = FileHelper::findFiles($this->basePath, [
            'filter' => (new PathMatcher())->only('**.php')->except('**/' . $this->ddlClassName . '.php')
        ]);
        sort($files);

        $error = '';
        $classList = [];
        $transaction = $this->db->beginTransaction();
        foreach ($files as $file) {
            $className = $this->getClassNameByFile($file);
            if (!class_exists($className)) {
                $this->throw("The class \"{$className}\" is not exists.");
            }
            if (in_array($className, $history)) {
                continue;
            }
            /** @var MigrateInterface $class */
            $class = new $className();
            if (!$class instanceof MigrateInterface) {
                $this->throw("The class \"{$className}\" is not implements \"" . MigrateInterface::class . "\".");
            }
            try {
                $class->up($this->builder);
                array_unshift($classList, $class);
            } catch (Throwable $t) {
                $error = "{$className}::up() failed.";
                $transaction->rollBack();
                break;
            }
        }

        if ($error) {
            foreach ($classList as $class) {
                /** @var MigrateInterface $class */
                try {
                    $class->down($this->builder);
                } catch (Throwable $t) {
                    $error .= "\n{$className}::down() failed.";
                }
            }
            return $error;
        } else {
            $count = count($classList);
            if ($count > 0) {
                $rows = [];
                $now = Date::fromUnix();
                foreach ($classList as $class) {
                    $rows[] = [get_class($class), $now];
                }

                $this->builder->batchInsert($this->tableName, ['version', ActiveRecord::CREATED_AT], $rows);

                $transaction->commit();

                return sprintf('Migrate count: %d.', $count);
            } else {
                return 'Already up to date.';
            }
        }
    }

    private function createFile(string $view, string $className, array $params = []): string
    {
        $namespace = $this->appNamespace . '\\' . $this->migratePath;

        $params['className'] = $className;
        $params['namespace'] = $namespace;

        if (@file_put_contents($this->basePath . '/' . $className . '.php', $this->generateService->render($view, $params))) {
            return sprintf('The file "%s.php" has been created in "%s".', $className, $this->basePath);
        } else {
            return 'Generate failed.';
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
        return sprintf('%s\\%s\\%s', $this->appNamespace, $this->migratePath, basename($file, '.php'));
    }

    private function createTable(): void
    {
        try {
            $this->builder->createTable($this->tableName, [
                'id' => $this->builder->primaryKey(),
                'version' => $this->builder->string(100)->notNull(),
                ActiveRecord::CREATED_AT => $this->builder->dateTime()->notNull()
            ]);
        } catch (Exception $t) {
            // do nothing.
        }
    }
}
