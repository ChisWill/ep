<?php

declare(strict_types=1);

namespace Ep\Db;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\Expression;

final class Service
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function getTables(string $prefix = ''): array
    {
        $query = Query::find($this->db);

        switch ($this->db->getDriverName()) {
            case 'mysql':
                $field = 'TABLE_NAME';
                $query
                    ->from('information_schema.TABLES')
                    ->where(['TABLE_SCHEMA' => new Expression('database()')]);
                break;
            case 'sqlite':
                $field = 'name';
                $query
                    ->from('sqlite_master')
                    ->where(['type' => 'table'])
                    ->andWhere(new Expression("`name` NOT LIKE 'sqlite_%'"));
                break;
        }

        if ($prefix) {
            $query->andWhere(new Expression("`{$field}` LIKE '{$prefix}%'"));
        }

        return $query->select($field)->column();
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
