<?php

declare(strict_types=1);

namespace Ep\Widget;

use Yiisoft\Db\Query\QueryInterface;

final class Paginator
{
    private QueryInterface $query;
    private int $page;
    private int $pageSize;
    private int $totalPage;
    private int $totalCount;
    private array $data;

    /**
     * @param QueryInterface $query
     */
    public function __construct(QueryInterface $query, int $page = 1, int $pageSize = 15)
    {
        $this->query = $query;
        $this->page = $page < 1 ? 1 : $page;
        $this->pageSize = $pageSize  < 1 ? 1 : $pageSize;
        $this->totalCount = (int) $this->query->count();
        $this->totalPage = (int) ceil($this->totalCount / $this->pageSize);
        $this->data = $this->paginate();
    }

    public function all(): array
    {
        return [
            'page' => $this->page,
            'pageSize' => $this->pageSize,
            'totalPage' => $this->totalPage,
            'totalCount' => $this->totalCount,
            'data' => $this->data
        ];
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getTotalPage(): int
    {
        return $this->totalPage;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function paginate(): array
    {
        return $this->query
            ->offset(($this->page - 1) * $this->pageSize)
            ->limit($this->pageSize)
            ->all();
    }
}
