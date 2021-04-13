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

    public function all(int $page = 1, int $pageSize = 15): array
    {
        $this->filter($page, $pageSize);

        $totalCount = (int) $this->query->count();

        return [
            'page' => $page,
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
            'totalPage' => (int) ceil($totalCount / $pageSize),
            'data' => $this->data($page, $pageSize)
        ];
    }

    public function data(int $page = 1, int $pageSize = 15): array
    {
        $this->filter($page, $pageSize);

        return $this->query
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->all();
    }

    private function filter(int &$page, int &$pageSize): void
    {
        $page = $page < 1 ? 1 : $page;
        $pageSize = $pageSize  < 1 ? 1 : $pageSize;
    }
}
