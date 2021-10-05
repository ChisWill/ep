<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\EnvInterface;
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;

final class Env implements EnvInterface
{
    private string $rootPath;
    private RepositoryInterface $repository;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->repository = $this->getRepository();

        Dotenv::create($this->repository, $rootPath)->safeLoad();
    }

    /**
     * {@inheritDoc}
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    private ?Config $config = null;

    /**
     * {@inheritDoc}
     */
    public function getConfig(): Config
    {
        if ($this->config === null) {
            $this->config = new Config(require($this->rootPath . '/config/main.php'));
        }
        return $this->config;
    }

    /**
     * {@inheritDoc}
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
            case 'empty':
                return '';
            case null:
                return $default;
            default:
                return $value;
        }
    }

    private function getRepository(): RepositoryInterface
    {
        return RepositoryBuilder::createWithDefaultAdapters()
            ->immutable()
            ->make();
    }
}
