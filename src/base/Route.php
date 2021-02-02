<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Helper\Alias;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\cachedDispatcher;

class Route
{
    private Config $config;
    private array $capture = [];

    public function __construct()
    {
        $this->config = Ep::getConfig();
    }

    public function match(string $path, string $method = 'GET'): array
    {
        return cachedDispatcher(function (RouteCollector $route) {
            $callback = $this->config->getRoute();
            if (is_callable($callback)) {
                call_user_func($callback, $route);
            }
            $route->addGroup($this->config->baseUrl, fn (RouteCollector $r) => $r->addRoute(...$this->config->defaultRoute));
        }, [
            'cacheFile' => Alias::get('@root/runtime/routeRules.cache'),
            'cacheDisabled' => $this->config->debug
        ])->dispatch($method, rtrim($path, '/') ?: '/');
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
        foreach ($intersect as $key => $value) {
            $this->capture['<' . $key . '>'] = trim($value, '/');
        }
        $handler = strtr($handler, $this->capture);
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
        $controllerName = sprintf('%s\\%s%s\\%s%s', $this->config->appNamespace, $prefix ? $prefix . '\\' : '', $this->config->controllerDirAndSuffix, ucfirst($controllerName), $this->config->controllerDirAndSuffix);
        $actionName .= $this->config->actionSuffix;
        return [$controllerName, $actionName];
    }

    public function getCapture(): array
    {
        return $this->capture;
    }
}
