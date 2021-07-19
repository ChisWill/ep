<?php

declare(strict_types=1);

namespace Ep\Command\Helper;

use Ep\Console\Service;
use Ep\Db\Query;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Schema\ColumnSchemaBuilder;
use Yiisoft\Db\Schema\SchemaBuilderTrait;

final class MigrateBuilder
{
    use SchemaBuilderTrait;

    private ConnectionInterface $db;
    private Service $service;

    public function __construct(ConnectionInterface $db, Service $service)
    {
        $this->db = $db;
        $this->service = $service;
    }

    protected function getDb(): ConnectionInterface
    {
        return $this->db;
    }

    public function execute(string $sql, array $params = []): void
    {
        $time = $this->begin('Execute SQL: ' . $sql);
        $this->db->createCommand($sql)->bindValues($params)->execute();
        $this->end($time);
    }

    public function insert(string $table, array $columns): void
    {
        $time = $this->begin("Insert into {$table}");
        $this->db->createCommand()->insert($table, $columns)->execute();
        $this->end($time);
    }

    public function batchInsert(string $table, array $columns, array $rows): void
    {
        $time = $this->begin("Batch insert into {$table}");
        $this->db->createCommand()->batchInsert($table, $columns, $rows)->execute();
        $this->end($time);
    }

    /**
     * @param array|Query $insertColumns
     * @param array|bool  $updateColumns
     */
    public function upsert(string $table, $insertColumns, $updateColumns = true, array $params = []): void
    {
        $time = $this->begin("Upsert into {$table}");
        $this->db->createCommand()->upsert($table, $insertColumns, $updateColumns, $params)->execute();
        $this->end($time);
    }

    /**
     * @param array|string $condition
     */
    public function update(string $table, array $columns, $condition = '', array $params = []): void
    {
        $time = $this->begin("Update {$table}");
        $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
        $this->end($time);
    }

    /**
     * @param array|string $condition
     */
    public function delete(string $table, $condition = '', array $params = []): void
    {
        $time = $this->begin("Delete from {$table}");
        $this->db->createCommand()->delete($table, $condition, $params)->execute();
        $this->end($time);
    }

    public function createTable(string $table, array $columns, ?string $options = null): void
    {
        $time = $this->begin("Create table {$table}");

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
        $time = $this->begin("Rename table {$table} to {$newName}");
        $this->db->createCommand()->renameTable($table, $newName)->execute();
        $this->end($time);
    }

    public function dropTable(string $table): void
    {
        $time = $this->begin("Drop table {$table}");
        $this->db->createCommand()->dropTable($table)->execute();
        $this->end($time);
    }

    public function truncateTable(string $table): void
    {
        $time = $this->begin("Truncate table {$table}");
        $this->db->createCommand()->truncateTable($table)->execute();
        $this->end($time);
    }

    /**
     * @param ColumnSchemaBuilder|string $type
     */
    public function addColumn(string $table, string $column, $type): void
    {
        $comment = null;
        if ($type instanceof ColumnSchemaBuilder) {
            $comment = $type->getComment();
            $type = $type->__toString();
        }

        $time = $this->begin("Add column {$column} {$type} to table {$table}");
        $this->db->createCommand()->addColumn($table, $column, $type)->execute();
        if ($comment !== null) {
            $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
        }
        $this->end($time);
    }

    public function dropColumn(string $table, string $column): void
    {
        $time = $this->begin("Drop column {$column} from table {$table}");
        $this->db->createCommand()->dropColumn($table, $column)->execute();
        $this->end($time);
    }

    public function renameColumn(string $table, string $name, string $newName): void
    {
        $time = $this->begin("Rename column {$name} in table {$table} to {$newName}");
        $this->db->createCommand()->renameColumn($table, $name, $newName)->execute();
        $this->end($time);
    }

    /**
     * @param ColumnSchemaBuilder|string $type
     */
    public function alterColumn(string $table, string $column, $type): void
    {
        $comment = null;

        if ($type instanceof ColumnSchemaBuilder) {
            $comment = $type->getComment();
            $type = $type->__toString();
        }

        $time = $this->begin("Alter column {$column} in table {$table} to {$type}");

        $this->db->createCommand()->alterColumn($table, $column, $type)->execute();

        if ($comment !== null) {
            $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
        }

        $this->end($time);
    }

    /**
     * @param array|string $columns
     */
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
        $time = $this->begin("Drop primary key {$name}");
        $this->db->createCommand()->dropPrimaryKey($name, $table)->execute();
        $this->end($time);
    }

    /**
     * @param array|string $columns
     */
    public function createIndex(string $name, string $table, $columns, bool $unique = false): void
    {
        $time = $this->begin('Create' . ($unique ? ' unique' : '') . " index {$name} on {$table} (" . implode(',', (array) $columns) . ')');
        $this->db->createCommand()->createIndex($name, $table, $columns, $unique)->execute();
        $this->end($time);
    }

    public function dropIndex(string $name, string $table): void
    {
        $time = $this->begin("Drop index {$name} on {$table}");
        $this->db->createCommand()->dropIndex($name, $table)->execute();
        $this->end($time);
    }

    public function addCommentOnColumn(string $table, string $column, string $comment): void
    {
        $time = $this->begin("Add comment on column {$column}");
        $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
        $this->end($time);
    }

    public function addCommentOnTable(string $table, string $comment): void
    {
        $time = $this->begin("Add comment on table {$table}");
        $this->db->createCommand()->addCommentOnTable($table, $comment)->execute();
        $this->end($time);
    }

    public function dropCommentFromColumn(string $table, string $column): void
    {
        $time = $this->begin("Drop comment from column {$column}");
        $this->db->createCommand()->dropCommentFromColumn($table, $column)->execute();
        $this->end($time);
    }

    public function dropCommentFromTable(string $table): void
    {
        $time = $this->begin("Drop comment from table {$table}");
        $this->db->createCommand()->dropCommentFromTable($table)->execute();
        $this->end($time);
    }

    public function find(): Query
    {
        return Query::find($this->db);
    }

    private function begin(string $message): float
    {
        $this->service->write(' - <info>' . $message . '</> ... ');

        return microtime(true);
    }

    private function end(float $time): void
    {
        $this->service->writeln('Done in <comment>' . sprintf('%.4f', microtime(true) - $time) . 's</>.');
    }
}
