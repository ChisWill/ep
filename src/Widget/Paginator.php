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
     * @param \Ep\Db\Query|\Ep\Db\ActiveQuery $query
     */
    public function __construct(QueryInterface $query, int $page, int $pageSize)
    {
        $this->query = $query;
        $this->page = $page < 1 ? 1 : $page;
        $this->pageSize = $pageSize  < 1 ? 1 : $pageSize;

        $this->init();
    }

    private function init(): void
    {
        $this->totalCount = (int) $this->query->count();
        $this->totalPage = (int) ceil($this->totalCount / $this->pageSize);
        $this->data = $this->query->paginate($this->page, $this->pageSize);
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
}
