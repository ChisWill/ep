<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Helper\Arr;
use Yiisoft\Di\Container;
use Yiisoft\Di\CompositeContainer;
use Psr\Container\ContainerInterface;

final class Ep
{
    private static ?ContainerInterface $_di = null;

    public static function setDi(array $definitions, array $providers = []): void
    {
        if (self::$_di === null) {
            $parent = new CompositeContainer();
            $parent->attach(new Container($definitions, $providers));
            self::$_di = $parent;
        } else {
            self::$_di->attach(new Container($definitions, $providers));
        }
    }

    public static function getDi(): ContainerInterface
    {
        return self::$_di;
    }

    private static ?Config $_config = null;

    public static function setConfig(array $config): void
    {
        if (self::$_config === null) {
            self::$_config = new Config($config);
        } else {
            foreach ($config as $name => $value) {
                self::$_config->$name = $value;
            }
        }
    }

    public static function getConfig(): Config
    {
        return self::$_config;
    }

    private static ?array $_params = null;

    /**
     * Get all parameters.
     * 
     * @return array
     */
    public static function getParams(): array
    {
        if (self::$_params === null) {
            self::$_params = self::$_config->getParams();
        }
        return self::$_params;
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
        return self::$_params[$name] ?? $default;
    }
}
