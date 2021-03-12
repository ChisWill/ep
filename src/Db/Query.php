<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Query\Query as BaseQuery;

class Query extends BaseQuery
{
    use QueryTrait;

    public static function find(?ConnectionInterface $db = null): Query
    {
        return new Query($db ?: Ep::getDb());
    }

    /**
     * @param array|Query $columns
     */
    public function insert(string $table, $columns): int
    {
        if (empty($columns)) {
            return 0;
        }
        return $this->createCommand()->insert($table, $columns)->execute();
    }

    public function batchInsert(string $table, array $columns, iterable $rows): int
    {
        if (empty($rows)) {
            return 0;
        }
        return $this->createCommand()->batchInsert($table, $columns, $rows)->execute();
    }

    /**
     * @param array|string $condition
     */
    public function update(string $table, array $columns, $condition = '', array $params = []): int
    {
        if (empty($columns)) {
            return 0;
        }
        return $this->createCommand()->update($table, $columns, $condition, $params)->execute();
    }

    /**
     * @param array|Query $insertColumns
     * @param array|bool  $updateColumns
     */
    public function upsert(string $table, $insertColumns, $updateColumns = true, array $params = []): int
    {
        if (empty($insertColumns)) {
            return 0;
        }
        return $this->createCommand()->upsert($table, $insertColumns, $updateColumns, $params)->execute();
    }

    /**
     * @param array|string $condition
     */
    public function delete(string $table, $condition = '', array $params = []): int
    {
        return $this->createCommand()->delete($table, $condition, $params)->execute();
    }

    /**
     * @param array|string $condition
     */
    public function increment(string $table, array $columns, $condition = '', array $params = []): int
    {
        foreach ($columns as $field => &$value) {
            if (is_numeric($value)) {
                $value = new Expression("`{$field}` + {$value}");
            }
        }
        return $this->update($table, $columns, $condition, $params);
    }
}
