<?php

declare(strict_types=1);

namespace Ep\Widget;

use Yiisoft\Db\Query\QueryInterface;

final class Paginator
{
    private QueryInterface $query;

    /**
     * @param QueryInterface $query
     */
    public function __construct(QueryInterface $query)
    {
        $this->query = $query;
    }

    public function all(int $page = 1, int $pageSize = 10): array
    {
        $this->normalize($page, $pageSize);

        $totalCount = (int) $this->query->count();

        return [
            'page' => $page,
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
            'totalPage' => (int) ceil($totalCount / $pageSize),
            'data' => $this->data($page, $pageSize)
        ];
    }

    public function data(int $page = 1, int $pageSize = 10): array
    {
        $this->normalize($page, $pageSize);

        return $this->query
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->all();
    }

    public function next(int $startId, int $pageSize = 10, string $primaryKey = 'id'): array
    {
        $this->normalize($startId, $pageSize);

        $data = $this->query
            ->andWhere(['>', $primaryKey, $startId])
            ->limit($pageSize)
            ->all();

        if ($data) {
            $nextId = end($data)[$primaryKey];
        } else {
            $nextId = $startId;
        }

        return compact('nextId', 'data');
    }

    private function normalize(int &...$values): void
    {
        foreach ($values as &$value) {
            $value = $value < 1 ? 1 : $value;
        }
    }
}
