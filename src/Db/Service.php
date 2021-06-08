<?php

declare(strict_types=1);

namespace Ep\Db;

use Yiisoft\Db\Connection\ConnectionInterface;

final class Service
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function getTables(string $prefix = ''): array
    {
        $tables = $this->db->getSchema()->getTableNames();
        if ($prefix) {
            $tables = array_filter($tables, static fn ($name): bool => strpos($name, $prefix) === 0);
        }
        return $tables;
    }

    public function getDDL(string $tableName): string
    {
        switch ($this->db->getDriverName()) {
            case 'mysql':
                $sql = <<<SQL
SHOW CREATE TABLE `{$tableName}`;
SQL;
                $field = 'Create Table';
                break;
            case 'sqlite':
                $sql = <<<SQL
SELECT `sql` FROM `sqlite_master` WHERE `type`='table' AND tbl_name='{$tableName}'
SQL;
                $field = 'sql';
                break;
        }

        return Query::find($this->db)
            ->createCommand()
            ->setRawSql($sql)
            ->queryOne()[$field];
    }
}
