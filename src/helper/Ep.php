<?php

namespace Ep\Helper;

use Ep\base\Config;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Container;

class Ep
{
    private static Config $_config;
    private static CompositeContainer $_di;

    public static function init(Config $config): void
    {
        static::$_config = $config;

        static::$_di = new CompositeContainer();
        static::$_di->attach(new Container($config->getDi()));
    }

    public static function getConfig(): Config
    {
        return static::$_config;
    }

    public static function getDi(): CompositeContainer
    {
        return static::$_di;
    }

    public static function createControllerName(string $nampspace, string $name): string
    {
        return $nampspace . '\\' . ucfirst($name) . 'Controller';
    }

    public static function parseControllerName(string $className): string
    {
        $pieces = explode('\\', $className);
        $name = array_pop($pieces);
        return lcfirst(str_replace('Controller', '', $name));
    }
}
