<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Contract\InjectorInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Redis\Connection as RedisConnection;
use Yiisoft\Di\Container as YiiContainer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class Ep
{
    public const VERSION = '1.0';

    private static ContainerInterface $container;

    public static function init(array $config = []): void
    {
        $config = new Config($config);

        self::$container = (new YiiContainer(
            $config->getDi() + require(dirname(__DIR__, 1) . '/config/definitions.php')
        ))
            ->get(ContainerInterface::class);

        AnnotationRegistry::registerLoader('class_exists');
    }

    public static function getDi(): ContainerInterface
    {
        return self::$container;
    }

    public static function getInjector(): InjectorInterface
    {
        return self::$container->get(InjectorInterface::class);
    }

    public static function getConfig(): Config
    {
        return self::$container->get(Config::class);
    }

    public static function getDb(?string $id = null): Connection
    {
        return self::$container->get($id ?: Connection::class);
    }

    public static function getRedis(?string $id = null): RedisConnection
    {
        return self::$container->get($id ?: RedisConnection::class);
    }

    public static function getCache(?string $id = null): CacheInterface
    {
        return self::$container->get($id ?: CacheInterface::class);
    }

    public static function getLogger(?string $id = null): LoggerInterface
    {
        return self::$container->get($id ?: LoggerInterface::class);
    }
}
