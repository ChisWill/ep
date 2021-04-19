<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep\Widget\Paginator;

trait QueryTrait
{
    public function getPaginator(): Paginator
    {
        return new Paginator($this);
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
        $command = $this->createCommand();

        tt(
            $command->getRawSql(),
            $command->getParams()
        );
    }
}
