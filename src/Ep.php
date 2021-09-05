<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Contract\InjectorInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Di\Container as YiiContainer;
use Yiisoft\Factory\Factory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class Ep
{
    public const VERSION = '1.0';

    private function __construct()
    {
    }

    private static ContainerInterface $container;
    private static Factory $factory;

    public static function init(array $config = []): ContainerInterface
    {
        $config = new Config($config);

        $definitions = $config->getDi() + require(dirname(__DIR__, 1) . '/config/definitions.php');

        self::$container = (new YiiContainer($definitions, [], [], $config->debug))->get(ContainerInterface::class);

        self::$factory = self::$container->get(Factory::class);
        self::$factory->setMultiple($definitions);

        AnnotationRegistry::registerLoader('class_exists');

        return self::$container;
    }

    public static function getDi(): ContainerInterface
    {
        return self::$container;
    }

    public static function getFactory(): Factory
    {
        return self::$factory;
    }

    public static function getInjector(): InjectorInterface
    {
        return self::$container->get(InjectorInterface::class);
    }

    public static function getConfig(): Config
    {
        return self::$container->get(Config::class);
    }

    public static function getDb(string $id = null): Connection
    {
        return self::$container->get($id ?? Connection::class);
    }

    public static function getCache(string $id = null): CacheInterface
    {
        return self::$container->get($id ?? CacheInterface::class);
    }

    public static function getLogger(string $id = null): LoggerInterface
    {
        return self::$container->get($id ?? LoggerInterface::class);
    }
}
