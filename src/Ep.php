<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Base\Constant;
use Ep\Contract\InjectorInterface;
use Ep\Kit\Annotate;
use Ep\Kit\Util;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Di\Container as YiiContainer;
use Yiisoft\Factory\Factory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

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

        self::bootstrap();

        return self::$container;
    }

    private static function bootstrap(): void
    {
        if (self::getConfig()->debug) {
            self::$container
                ->get(Annotate::class)
                ->cache(
                    self::$container
                        ->get(Util::class)
                        ->getClassList(self::getConfig()->rootNamespace)
                );
        }

        foreach (self::getCache()->get(Constant::CACHE_ANNOTATION_CONFIGURE_DATA) ?: [] as $class => $data) {
            foreach (call_user_func([$class, 'handlers']) as $handler) {
                self::$container->get($handler)->bootstrap($data);
            }
        }
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
