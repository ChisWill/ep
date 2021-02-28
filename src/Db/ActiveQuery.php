<?php

declare(strict_types=1);

namespace Ep\Db;

use Yiisoft\ActiveRecord\ActiveQuery as BaseActiveQuery;

class ActiveQuery extends BaseActiveQuery
{
    use QueryTrait;
}
