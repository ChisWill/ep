<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep\Helper\Batch;
use Ep\Widget\Paginator;
use Yiisoft\ActiveRecord\ActiveQuery as YiiActiveQuery;
use Yiisoft\Db\Expression\Expression;
use InvalidArgumentException;

final class ActiveQuery extends YiiActiveQuery
{
    use QueryTrait;

    public function update(array $columns): int
    {
        return $this->createCommand()
            ->update(current($this->getFrom()), $columns, $this->getWhere(), $this->getParams())
            ->execute();
    }

    public function increment(array $columns): int
    {
        foreach ($columns as $field => &$value) {
            if (is_numeric($value)) {
                $value = new Expression("`{$field}` + {$value}");
            }
        }
        return $this->update($columns);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function reduce(int &$startId, callable ...$callbacks): array
    {
        if (count($callbacks) === 0) {
            throw new InvalidArgumentException('It must be at least one callback.');
        }

        return Batch::reduce($this->getBatchProducer($this->getPrimaryKey(), $startId), ...$callbacks);
    }

    public function nextPage(int $startId, int $pageSize = 10): array
    {
        return (new Paginator($this))->next($startId, $pageSize, $this->getPrimaryKey());
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getPrimaryKey(): string
    {
        $primaryKey = $this->getARClass()::PK;
        if (is_array($primaryKey)) {
            throw new InvalidArgumentException('Don\'t support composite primary key.');
        }

        return $primaryKey;
    }
}
