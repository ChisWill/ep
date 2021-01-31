<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Helper\Alias;
use Ep\Helper\Arr;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use RuntimeException;

use function FastRoute\cachedDispatcher;

class Router
{
    private Config $config;

    public function __construct()
    {
        $this->config = Ep::getConfig();
    }

    public function match(string $path, string $method = 'GET'): array
    {
        $path = rtrim($path, '/') ?: '/';
        $dispatcher = cachedDispatcher(function (RouteCollector $route) {
            $callback = $this->config->getRouter();
            if (is_callable($callback)) {
                call_user_func($callback, $route);
            }
            $route->addGroup($this->config->baseUrl, function (RouteCollector $r) {
                $r->addRoute(['GET', 'POST'], '{prefix:[\w/]*?}{controller:/?[a-zA-Z]\w*|}{action:/?[a-zA-Z]\w*|}', '<prefix>/<controller>/<action>');
            });
        }, [
            'cacheFile' => Alias::get('@root/runtime/routeRules.cache'),
            'cacheDisabled' => $this->config->debug
        ]);
        return $dispatcher->dispatch($method, $path);
    }

    public function solveRouteInfo(array $routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $params = [];
                $handler = $this->config->errorHandler;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $params = [];
                $handler = $this->config->errorHandler;
                break;
            case Dispatcher::FOUND:
                [$handler, $params] = $this->replaceHandler($routeInfo[1], $routeInfo[2]);
                break;
        }
        return [$handler, $params];
    }

    private function replaceHandler($handler, $params)
    {
        preg_match_all('/<(\w+)>/', $handler, $matches);
        $match = array_flip($matches[1]);
        $intersect = array_intersect_key($params, $match);
        $params = array_diff_key($params, $match);
        $replace = [];
        foreach ($intersect as $key => $value) {
            $replace['<' . $key . '>'] = trim($value, '/');
        }
        $handler = strtr($handler, $replace);
        return [$handler, $params];
    }

    public function parseHandler(string $handler): array
    {
        $pieces = explode('/', $handler);
        $prefix = '';
        switch (count($pieces)) {
            case 0:
                $controllerName = $this->config->defaultController;
                $actionName = $this->config->defaultAction;
                break;
            case 1:
                $controllerName = $pieces[0];
                $actionName = $this->config->defaultAction;
                break;
            default:
                $actionName = array_pop($pieces) ?: $this->config->defaultAction;
                $controllerName = array_pop($pieces) ?: $this->config->defaultController;
                $prefix = implode('\\', $pieces);
                break;
        }
        $controllerName = sprintf('%s\\%s%s\\%sController', $this->config->appNamespace, $prefix ? $prefix . '\\' : '', $this->config->controllerDirname, ucfirst($controllerName));
        return [$controllerName, $actionName];
    }
}
