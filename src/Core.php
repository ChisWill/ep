<?php

namespace ep;

use ep\base\Config;
use ep\web\Config as WebConfig;
use ep\web\Exception;
use ep\web\Request;

class Core
{
    private static Core $_instance;

    public function __construct()
    {
        define('EP_PATH', __DIR__);
        require_once 'functions.php';
        self::$_instance = $this;
    }

    public static function getInstance()
    {
        return self::$_instance;
    }

    private Config $_config;

    public function getConfig()
    {
        return $this->_config;
    }

    public function run(Config $config)
    {
        $this->_config = $config;
        switch (get_class($config)) {
            case 'ep\web\Config':
                $this->handleWebRoute($config);
                break;
            case 'ep\console\Config':
                break;
        }
    }

    private function handleWebRoute(WebConfig $config)
    {
        $request = new Request($config);
        $requestPath = $request->getRequestPath();
        if (count($config->routeRules) > 0) {
            $requestPath = $request->solveRouteRules($config->routeRules, $requestPath);
        }
        [$controllerName, $actionName] = $request->solvePath($requestPath);
        if (!class_exists($controllerName)) {
            throw new Exception('NOT FOUND', 404);
        }
        $controller = new $controllerName;
        if (!method_exists($controller, $actionName)) {
            throw new Exception('NOT FOUND', 404);
        }
        $controller->$actionName($request);
    }

    private static $aliases = [];

    public static function setAlias($alias, $path)
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

    public static function getAlias($alias)
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
}
