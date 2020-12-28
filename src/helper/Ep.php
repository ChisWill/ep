<?php

namespace ep\helper;

use Exception;
use ep\base\Config;
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

    private static $aliases = [];

    public static function setAlias(string $alias, string  $path): void
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

    public static function getAlias(string $alias): string
    {
        if (strncmp($alias, '@', 1)) {
            return $alias;
        }

        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            } else {
                foreach (static::$aliases[$root] as $name => $path) {
                    if (strpos($alias . '/', $name . '/') === 0) {
                        return $path . substr($alias, strlen($name));
                    }
                }
            }
        }

        throw new Exception("Invalid path alias: $alias");
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
