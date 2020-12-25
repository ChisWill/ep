<?php

namespace ep\base;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Cache\QueryCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Log\Logger;
use Yiisoft\Profiler\Profiler;

abstract class Config
{
    public string $defaultController = 'index';
    public string $defaultAction = 'index';
    public string $controllerNamespace = 'src\\controller';
    public string $viewFilePath = '@root/view';

    public array $routeRules = [];

    public array $mysql = [
        'dsn' => '',
        'username' => '',
        'password' => '',
    ];

    private array $definitions;

    public function __construct()
    {
        $this->setDefaultDi();
    }

    private function setDefaultDi(): void
    {
        $this->definitions = [
            LoggerInterface::class => [
                '__class' => Logger::class,
            ],
            CacheInterface::class => [
                '__class' => Cache::class,
                '__construct()' => [new ArrayCache]
            ],
            'db' => function (ContainerInterface $container) {
                $connection = new Connection(
                    $container->get(LoggerInterface::class),
                    $container->get(Profiler::class),
                    $container->get(QueryCache::class),
                    $container->get(SchemaCache::class),
                    $this->mysql['dsn']
                );

                $connection->setUsername($this->mysql['username']);
                $connection->setPassword($this->mysql['password']);

                return $connection;
            }
        ];
    }

    protected function setDi(array $definitions = []): void
    {
        $this->definitions += $definitions;
    }

    public function getDi()
    {
        return $this->definitions;
    }
}
