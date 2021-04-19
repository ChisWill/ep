<?php

declare(strict_types=1);

namespace Ep\Db;

use Yiisoft\ActiveRecord\ActiveQuery as BaseActiveQuery;
use Yiisoft\Db\Expression\Expression;

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
}
