<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep\Helper\Batch;
use Yiisoft\ActiveRecord\ActiveQuery as BaseActiveQuery;
use Yiisoft\Db\Expression\Expression;
use LogicException;

class ActiveQuery extends BaseActiveQuery
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
     * @return mixed
     * @throws LogicException
     */
    public function reduce(int &$startId, callable ...$callbacks)
    {
        $primaryKey = $this->getARClass()::PK;
        if (is_array($primaryKey)) {
            throw new LogicException('Don\'t support composite primary key.');
        }
        if (count($callbacks) === 0) {
            throw new LogicException('It must be at least one callback.');
        }

        return Batch::reduce($this->getBatchProducer($primaryKey, $startId), ...$callbacks);
    }
}
