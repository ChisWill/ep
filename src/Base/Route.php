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

final class Route implements RouteInterface
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
            'cacheFile' => Alias::get($this->config->runtimeDir . '/route.cache'),
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

    private function replaceHandler($handler, array $params): array
    {
        if (is_array($handler)) {
            return [$handler, $params];
        }
        preg_match_all('/<(\w+)>/', $handler, $matches);
        $match = array_flip($matches[1]);
        $intersect = array_intersect_key($params, $match);
        $params = array_diff_key($params, $match);
        foreach ($intersect as $key => &$value) {
            $value = strtolower($value);
            $this->captureParams['<' . $key . '>'] = trim($value, '/');
        }
        $handler = strtr($handler, $this->captureParams);
        return [$handler, $params];
    }

    public function parseHandler($handler): array
    {
        if (is_array($handler)) {
            return $handler;
        }
        $pieces = explode('/', $handler);
        $prefix = '';
        switch (count($pieces)) {
            case 0:
                $controller = $this->config->defaultController;
                $action = $this->config->defaultAction;
                break;
            case 1:
                $controller = $pieces[0];
                $action = $this->config->defaultAction;
                break;
            default:
                $action = array_pop($pieces) ?: $this->config->defaultAction;
                $controller = array_pop($pieces) ?: $this->config->defaultController;
                $prefix = implode('\\', $pieces);
                break;
        }
        if ($prefix) {
            $ns = strpos($prefix, '\\\\') === false ? $prefix . '\\' . $this->config->controllerDirAndSuffix : str_replace('\\\\', '\\' . $this->config->controllerDirAndSuffix . '\\', $prefix);
        } else {
            $ns = $this->config->controllerDirAndSuffix;
        }
        $controller = sprintf('%s\\%s\\%s', $this->config->appNamespace, $ns, ucfirst($controller) . $this->config->controllerDirAndSuffix);
        return [$controller, $action];
    }

    /**
     * @throws RuntimeException
     */
    public function createController(string $class): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new RuntimeException("{$class} is not found.");
        }
        return Ep::getDi()->get($class);
    }

    public function getCaptureParams(): array
    {
        return $this->captureParams;
    }
}
