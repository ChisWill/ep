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
    private string $baseUrl;

    public function __construct()
    {
        $this->config = Ep::getConfig();
        $this->baseUrl = PHP_SAPI === 'cli' ? '/' : $this->config->baseUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function match(string $path, string $method = 'GET'): array
    {
        return $this->solveRouteInfo(
            cachedDispatcher(function (RouteCollector $route) {
                $callback = $this->config->getRoute();
                $callback !== null && call_user_func($callback, $route);
                $route->addGroup($this->baseUrl, fn (RouteCollector $r) => $r->addRoute(...$this->config->defaultRoute));
            }, [
                'cacheFile' => Alias::get($this->config->runtimeDir . '/route.cache'),
                'cacheDisabled' => $this->config->debug
            ])->dispatch($method, rtrim($path, '/') ?: '/')
        );
    }

    private function solveRouteInfo(array $routeInfo): array
    {
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                [$handler, $params] = $this->replaceHandler($routeInfo[1], $routeInfo[2]);
                break;
            case Dispatcher::NOT_FOUND:
                $params = [];
                $handler = $this->config->notFoundHandler;
                break;
            default:
                $params = [];
                $handler = $this->config->notFoundHandler;
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
}
