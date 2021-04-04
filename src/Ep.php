<?php

declare(strict_types=1);

use Ep\Base\Config;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Di\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class Ep
{
    public static function init(array $config = []): void
    {
        $config = new Config($config);

        self::$di = new Container($config->getDi() + require(dirname(__DIR__, 1) . '/config/definitions.php'));
    }

    private static ContainerInterface $di;

    public static function getDi(): ContainerInterface
    {
        return self::$di;
    }

    public static function getConfig(): Config
    {
        return self::$di->get(Config::class);
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
