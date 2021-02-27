<?php

declare(strict_types=1);

namespace Ep\Db;

use Yiisoft\ActiveRecord\ActiveQuery as YiiActiveQuery;

class ActiveQuery extends YiiActiveQuery
{
    public function getRawSql(): string
    {
        return $this->createCommand()->getRawSql();
    }
}
