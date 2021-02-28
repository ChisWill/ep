<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query as BaseQuery;

class Query extends BaseQuery
{
    use QueryTrait;

    public static function find(?ConnectionInterface $db = null): Query
    {
        return new Query($db ?: Ep::getDb());
    }
}
