<?php

declare(strict_types=1);

namespace Ep\Command\Helper;

use Ep\Db\Query;
use Ep\Helper\Str;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Schema\ColumnSchemaBuilder;
use Yiisoft\Db\Schema\SchemaBuilderTrait;

final class MigrateBuilder
{
    use SchemaBuilderTrait;

    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    protected function getDb(): ConnectionInterface
    {
        return $this->db;
    }

    public function execute(string $sql, array $params = []): void
    {
        $time = $this->begin('execute SQL: ' . Str::subtext($sql, 50));
        $this->db->createCommand($sql)->bindValues($params)->execute();
        $this->end($time);
    }

    public function insert(string $table, array $columns): void
    {
        $time = $this->begin("insert into {$table}");
        $this->db->createCommand()->insert($table, $columns)->execute();
        $this->end($time);
    }

    public function batchInsert(string $table, array $columns, array $rows): void
    {
        $time = $this->begin("batch insert into {$table}");
        $this->db->createCommand()->batchInsert($table, $columns, $rows)->execute();
        $this->end($time);
    }

    public function upsert(string $table, $insertColumns, $updateColumns = true, array $params = []): void
    {
        $time = $this->begin("upsert into {$table}");
        $this->db->createCommand()->upsert($table, $insertColumns, $updateColumns, $params)->execute();
        $this->end($time);
    }

    public function update(string $table, array $columns, $condition = '', array $params = []): void
    {
        $time = $this->begin("update {$table}");
        $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
        $this->end($time);
    }

    public function delete(string $table, $condition = '', array $params = []): void
    {
        $time = $this->begin("delete from {$table}");
        $this->db->createCommand()->delete($table, $condition, $params)->execute();
        $this->end($time);
    }

    public function createTable(string $table, array $columns, ?string $options = null): void
    {
        $time = $this->begin("create table {$table}");

        $this->db->createCommand()->createTable($table, $columns, $options)->execute();

        foreach ($columns as $column => $type) {
            if ($type instanceof ColumnSchemaBuilder) {
                $comment = $type->getComment();
                if ($comment !== null) {
                    $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
                }
            }
        }

        $this->end($time);
    }

    public function renameTable(string $table, string $newName): void
    {
        $time = $this->begin("rename table {$table} to {$newName}");
        $this->db->createCommand()->renameTable($table, $newName)->execute();
        $this->end($time);
    }

    public function dropTable(string $table): void
    {
        $time = $this->begin("drop table {$table}");
        $this->db->createCommand()->dropTable($table)->execute();
        $this->end($time);
    }

    public function truncateTable(string $table): void
    {
        $time = $this->begin("truncate table {$table}");
        $this->db->createCommand()->truncateTable($table)->execute();
        $this->end($time);
    }

    public function addColumn(string $table, string $column, $type): void
    {
        $comment = null;
        if ($type instanceof ColumnSchemaBuilder) {
            $comment = $type->getComment();
            $type = $type->__toString();
        }

        $time = $this->begin("add column {$column} {$type} to table {$table}");
        $this->db->createCommand()->addColumn($table, $column, $type)->execute();
        if ($comment !== null) {
            $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
        }
        $this->end($time);
    }

    public function dropColumn(string $table, string $column): void
    {
        $time = $this->begin("drop column {$column} from table {$table}");
        $this->db->createCommand()->dropColumn($table, $column)->execute();
        $this->end($time);
    }

    public function renameColumn(string $table, string $name, string $newName): void
    {
        $time = $this->begin("rename column {$name} in table {$table} to {$newName}");
        $this->db->createCommand()->renameColumn($table, $name, $newName)->execute();
        $this->end($time);
    }

    public function alterColumn(string $table, string $column, $type): void
    {
        $comment = null;

        if ($type instanceof ColumnSchemaBuilder) {
            $comment = $type->getComment();
            $type = $type->__toString();
        }

        $time = $this->begin("alter column {$column} in table {$table} to {$type}");

        $this->db->createCommand()->alterColumn($table, $column, $type)->execute();

        if ($comment !== null) {
            $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
        }

        $this->end($time);
    }

    public function addPrimaryKey(string $name, string $table, $columns): void
    {
        $time = $this->begin(
            "Add primary key {$name} on {$table} (" . (is_array($columns) ? implode(',', $columns) : $columns) . ')'
        );
        $this->db->createCommand()->addPrimaryKey($name, $table, $columns)->execute();
        $this->end($time);
    }

    public function dropPrimaryKey(string $name, string $table): void
    {
        $time = $this->begin("drop primary key {$name}");
        $this->db->createCommand()->dropPrimaryKey($name, $table)->execute();
        $this->end($time);
    }

    public function createIndex(string $name, string $table, $columns, bool $unique = false): void
    {
        $time = $this->begin('create' . ($unique ? ' unique' : '') . " index {$name} on {$table} (" . implode(',', (array) $columns) . ')');
        $this->db->createCommand()->createIndex($name, $table, $columns, $unique)->execute();
        $this->end($time);
    }

    public function dropIndex(string $name, string $table): void
    {
        $time = $this->begin("drop index {$name} on {$table}");
        $this->db->createCommand()->dropIndex($name, $table)->execute();
        $this->end($time);
    }

    public function addCommentOnColumn(string $table, string $column, string $comment): void
    {
        $time = $this->begin("add comment on column {$column}");
        $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
        $this->end($time);
    }

    public function addCommentOnTable(string $table, string $comment): void
    {
        $time = $this->begin("add comment on table {$table}");
        $this->db->createCommand()->addCommentOnTable($table, $comment)->execute();
        $this->end($time);
    }

    public function dropCommentFromColumn(string $table, string $column): void
    {
        $time = $this->begin("drop comment from column {$column}");
        $this->db->createCommand()->dropCommentFromColumn($table, $column)->execute();
        $this->end($time);
    }

    public function dropCommentFromTable(string $table): void
    {
        $time = $this->begin("drop comment from table {$table}");
        $this->db->createCommand()->dropCommentFromTable($table)->execute();
        $this->end($time);
    }

    public function query(): Query
    {
        return Query::find($this->db);
    }

    private function begin(string $message): float
    {
        return microtime(true);
    }

    private function end(float $time): void
    {
    }
}
