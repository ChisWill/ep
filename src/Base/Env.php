<?php

declare(strict_types=1);

namespace Ep\Base;

use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;

final class Env
{
    private RepositoryInterface $repository;

    public function __construct(string $basePath)
    {
        $this->repository = $this->getRepository();

        Dotenv::create($this->repository, $basePath)->safeLoad();
    }

    private function getRepository(): RepositoryInterface
    {
        return RepositoryBuilder::createWithDefaultAdapters()
            ->immutable()
            ->make();
    }

    /**
     * @param  mixed $default
     * 
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        switch ($value = $this->repository->get($key)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            case null:
                return $default;
            default:
                return $value;
        }
    }
}
