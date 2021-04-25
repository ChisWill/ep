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
                $query
                    ->select('TABLE_NAME')
                    ->from('information_schema.TABLES')
                    ->where(['TABLE_SCHEMA' => new Expression('database()')]);
                $field = 'TABLE_NAME';
                break;
            case 'sqlite':
                $query
                    ->select('name')
                    ->from('sqlite_master')
                    ->where(['type' => 'table'])
                    ->andWhere(new Expression("`name` NOT LIKE 'sqlite_%'"));
                $field = 'name';
                break;
        }

        if ($prefix) {
            $query->andWhere(new Expression("`{$field}` LIKE '{$prefix}%'"));
        }

        return $query->column();
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
