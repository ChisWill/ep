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
    use ConfigurableTrait;

    private Config $config;
    private string $baseUrl;
    private string $controllerSuffix;

    /**
     * {@inheritDoc}
     */
    public function matchRequest(string $path, string $method = 'GET'): array
    {
        return cachedDispatcher(function (RouteCollector $route) {
            $callback = $this->config->getRoute();
            if (is_callable($callback)) {
                call_user_func($callback, $route);
            }
            $route->addGroup($this->baseUrl, fn (RouteCollector $r) => $r->addRoute(...$this->config->defaultRoute));
        }, [
            'cacheFile' => Alias::get($this->config->runtimeDir . '/route.cache'),
            'cacheDisabled' => $this->config->debug
        ])->dispatch($method, rtrim($path, '/') ?: '/');
    }

    /**
     * {@inheritDoc}
     */
    public function solveRouteInfo(array $routeInfo): array
    {
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                [$handler, $params] = $this->replaceHandler($routeInfo[1], $routeInfo[2]);
                break;
            case Dispatcher::NOT_FOUND:
                $params = [];
                $handler = $this->config->errorHandler;
                break;
            default:
                $params = [];
                $handler = $this->config->errorHandler;
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
        $captureParams = [];
        foreach ($intersect as $key => &$value) {
            $value = strtolower($value);
            $captureParams['<' . $key . '>'] = trim($value, '/');
        }
        $handler = strtr($handler, $captureParams);
        return [$handler, $params];
    }

    /**
     * {@inheritDoc}
     */
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
            $ns = strpos($prefix, '\\\\') === false ? $prefix . '\\' . $this->controllerSuffix : str_replace('\\\\', '\\' . $this->controllerSuffix . '\\', $prefix);
        } else {
            $ns = $this->controllerSuffix;
        }
        $controller = sprintf('%s\\%s\\%s', $this->config->appNamespace, $ns, ucfirst($controller) . $this->controllerSuffix);
        return [$controller, $action];
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function createController(string $class): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new RuntimeException("{$class} is not found.");
        }
        return Ep::getDi()->get($class);
    }
}
