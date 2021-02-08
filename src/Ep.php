<?php

declare(strict_types=1);

use Ep\Base\Config;
use Yiisoft\Di\Container;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class Ep
{
    private static ContainerInterface $di;

    private static Config $config;

    private static array $params;

    public static function init(array $config = [])
    {
        self::$di = new CompositeContainer();
        self::$config = new Config($config);
        self::$params = self::$config->getParams();
    }

    public static function setDi(array $definitions = [], array $providers = []): void
    {
        self::$di->attach(new Container($definitions, $providers));
    }

    public static function getDi(): ContainerInterface
    {
        return self::$di;
    }

    public static function getInjector(): Injector
    {
        return new Injector(self::getDi());
    }

    public static function getLogger(?string $name = null): LoggerInterface
    {
        return self::$di->get($name ?: LoggerInterface::class);
    }

    public static function setConfig(array $config): void
    {
        foreach ($config as $name => $value) {
            self::$config->$name = $value;
        }
    }

    public static function getConfig(): Config
    {
        return self::$config;
    }

    /**
     * Get all parameters.
     * 
     * @return array
     */
    public static function getParams(): array
    {
        return self::$params;
    }

    /**
     * Get single parameter.
     * 
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public static function getParam(string $name, $default = null)
    {
        return self::$params[$name] ?? $default;
    }
}
