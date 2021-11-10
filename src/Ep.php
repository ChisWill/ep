<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Base\Constant;
use Ep\Base\Env;
use Ep\Contract\BootstrapInterface;
use Ep\Contract\EnvInterface;
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

    private static Env $env;
    private static Config $config;
    private static ContainerInterface $container;
    private static Factory $factory;

    private function __construct()
    {
    }

    private static bool $init = false;

    /**
     * @param EnvInterface|string $envOrRootPath
     */
    public static function init($envOrRootPath): ContainerInterface
    {
        if (self::$init) {
            return self::$container;
        }
        self::$init = true;

        self::$env = $envOrRootPath instanceof EnvInterface ? $envOrRootPath : new Env($envOrRootPath);
        self::$config = self::$env->getConfig();

        $definitions = self::$config->getDi() + require(dirname(__DIR__) . '/config/definitions.php');

        self::$container = (new YiiContainer($definitions, [], [], self::$config->debug))->get(ContainerInterface::class);
        self::$factory = self::$container->get(Factory::class);
        self::$factory->setMultiple($definitions);

        AnnotationRegistry::registerLoader('class_exists');

        if (!self::$config->isEp()) {
            self::bootstrap();
        }

        return self::$container;
    }

    private static function bootstrap(): void
    {
        foreach (self::getCache()->get(Constant::CACHE_ANNOTATION_CONFIGURE_DATA) ?: [] as $class => $data) {
            $instance = self::$container->get(call_user_func([$class, 'handler']));
            if ($instance instanceof BootstrapInterface) {
                $instance->bootstrap($data);
            } else {
                throw new LogicException(sprintf('The class %s is not implements %s.', get_class($instance), BootstrapInterface::class));
            }
        }
    }

    public static function getEnv(): Env
    {
        return self::$env;
    }

    public static function getConfig(): Config
    {
        return self::$config;
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

    public static function scan(): void
    {
        self::$container
            ->get(Annotate::class)
            ->cache(
                self::$container
                    ->get(Util::class)
                    ->getClassList(self::$config->rootNamespace)
            );
    }
}
