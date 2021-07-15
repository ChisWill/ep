<?php

declare(strict_types=1);

namespace Ep\Db;

use Ep\Widget\Paginator;
use Closure;

trait QueryTrait
{
    public function getPaginator(): Paginator
    {
        return new Paginator($this);
    }

    public function paginate(int $page, int $pageSize = 15): array
    {
        return (new Paginator($this))->data($page, $pageSize);
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

    protected function getBatchProducer(string $primaryKey, int &$startId): Closure
    {
        return function () use ($primaryKey, &$startId): array {
            $query = clone $this;
            $select = $query->getSelect();
            if ($select && !in_array($primaryKey, $select)) {
                $query->addSelect($primaryKey);
            }
            $data = $query
                ->andWhere(['>', $primaryKey, $startId])
                ->orderBy($primaryKey)
                ->all();
            if ($data) {
                $pos = strpos($primaryKey, '.');
                if ($pos !== false) {
                    $primaryKey = substr($primaryKey, $pos + 1);
                }
                $startId = max(array_column($data, $primaryKey));
            }
            return $data;
        };
    }
}
