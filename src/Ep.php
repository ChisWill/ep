<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Helper\Alias;
use Ep\Helper\Arr;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Container;
use Psr\Log\LoggerInterface;

final class Ep
{
    private static Config $config;

    private static CompositeContainer $di;

    public static function init(array $config = []): void
    {
        self::$config = new Config($config);

        Alias::set('@root', self::$config->rootPath);
        Alias::set('@vendor', self::$config->vendorPath);
        Alias::set('@ep', dirname(__DIR__, 1));

        self::$di = new CompositeContainer();
        self::$di->attach(new Container(
            Arr::merge(
                require(Alias::get('@ep/config/definitions.php')),
                self::$config->definitions
            )
        ));
    }

    public static function getConfig(): Config
    {
        return self::$config;
    }

    public static function getDi(): CompositeContainer
    {
        return self::$di;
    }

    public static function getDb(?string $id = null): Connection
    {
        return self::$di->get($id ?: Connection::class);
    }

    public static function getRedis(?string $id = null): RedisConnection
    {
        return self::$di->get($id ?: RedisConnection::class);
    }

    public static function getCache(?string $id = null): CacheInterface
    {
        return self::$di->get($id ?: CacheInterface::class);
    }

    public static function getLogger(?string $id = null): LoggerInterface
    {
        return self::$di->get($id ?: LoggerInterface::class);
    }
}
