<?php

namespace Ep\Db;

class ActiveQuery extends \Yiisoft\ActiveRecord\ActiveQuery
{
    public function getRawSql(): string
    {
        return $this->createCommand()->getRawSql();
    }
}
