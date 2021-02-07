<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Helper\Alias;
use Ep\Standard\ControllerInterface;
use Ep\Standard\RouteInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use RuntimeException;

use function FastRoute\cachedDispatcher;

class Route implements RouteInterface
{
    private Config $config;
    private array $captureParams = [];

    public function __construct()
    {
        $this->config = Ep::getConfig();
    }

    public function matchRequest(string $path, string $method = 'GET'): array
    {
        return cachedDispatcher(function (RouteCollector $route) {
            $callback = $this->config->getRoute();
            if (is_callable($callback)) {
                call_user_func($callback, $route);
            }
            $route->addGroup($this->config->baseUrl, fn (RouteCollector $r) => $r->addRoute(...$this->config->defaultRoute));
        }, [
            'cacheFile' => Alias::get($this->config->runtimeDir . '/__route/cacheFile'),
            'cacheDisabled' => $this->config->debug
        ])->dispatch($method, rtrim($path, '/') ?: '/');
    }

    public function solveRouteInfo(array $routeInfo): array
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

    private function replaceHandler(string $handler, array $params): array
    {
        preg_match_all('/<(\w+)>/', $handler, $matches);
        $match = array_flip($matches[1]);
        $intersect = array_intersect_key($params, $match);
        $params = array_diff_key($params, $match);
        foreach ($intersect as $key => $value) {
            $this->captureParams['<' . $key . '>'] = trim($value, '/');
        }
        $handler = strtr($handler, $this->captureParams);
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
                $controllerName = strtolower($pieces[0]);
                $actionName = $this->config->defaultAction;
                break;
            default:
                $actionName = array_pop($pieces) ?: $this->config->defaultAction;
                $controllerName = array_pop($pieces) ?: $this->config->defaultController;
                $prefix = implode('\\', $pieces);
                break;
        }
        $controllerName = sprintf('%s\\%s%s\\%s%s', $this->config->appNamespace, $prefix ? $prefix . '\\' : '', $this->config->controllerDirAndSuffix, ucfirst(strtolower($controllerName)), $this->config->controllerDirAndSuffix);
        $actionName = strtolower($actionName) . $this->config->actionSuffix;
        return [$controllerName, $actionName];
    }

    public function createController(string $controllerName): ControllerInterface
    {
        if (!class_exists($controllerName)) {
            throw new RuntimeException("{$controllerName} is not found.");
        }
        return Ep::getDi()->get($controllerName);
    }

    public function getCaptureParams(): array
    {
        return $this->captureParams;
    }
}
