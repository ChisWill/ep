<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep\Widget\Paginator;

trait QueryTrait
{
    public function getPaginator(int $page, int $pageSize = 15): Paginator
    {
        return new Paginator($this, $page, $pageSize);
    }

    public function paginate(int $page, int $pageSize = 15): array
    {
        return $this
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->all();
    }

    public function map(string $key, string $value): array
    {
        if (strpos($key, '.') === false) {
            $column = $key;
        } else {
            $column = explode('.', $key)[1];
        }
        return $this
            ->select([$value, $key])
            ->indexBy($column)
            ->asArray()
            ->column();
    }

    public function getRawSql(): string
    {
        return $this->createCommand()->getRawSql();
    }

    public function dump(): void
    {
        test($this->getRawSql(), $this->createCommand()->getParams());
    }
}
