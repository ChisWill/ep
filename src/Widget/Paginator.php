<?php

declare(strict_types=1);

namespace Ep\Widget;

use Ep\Db\Query;

final class Paginator
{
    private Query $query;
    private int $page;
    private int $pageSize;
    private int $totalPage;
    private int $totalCount;
    private array $data;

    /**
     * @param Query $query
     */
    public function __construct(Query $query, int $page, int $pageSize)
    {
        $this->query = $query;
        $this->page = $page < 1 ? 1 : $page;
        $this->pageSize = $pageSize  < 1 ? 1 : $pageSize;

        $this->execute();
    }

    private function execute(): void
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
