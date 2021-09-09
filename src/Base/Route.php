<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Annotation\Route as AnnotationRoute;
use Ep\Contract\NotFoundException;
use Ep\Kit\Annotate;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Http\Method;
use Closure;

use function FastRoute\cachedDispatcher;

final class Route
{
    private const DEFAULT_ROUTE_RULE = [
        Method::ALL,
        '{prefix:[\w/-]*?}{controller:/?[a-zA-Z][\w-]*|}{action:/?[a-zA-Z][\w-]*|}',
        '<prefix>/<controller>/<action>'
    ];

    private Config $config;
    private Aliases $aliases;
    private Annotate $annotate;

    public function __construct(
        Config $config,
        Aliases $aliases,
        Annotate $annotate
    ) {
        $this->config = $config;
        $this->aliases = $aliases;
        $this->annotate = $annotate;
    }

    private string $baseUrl = '';

    public function withBaseUrl(string $baseUrl): self
    {
        $new = clone $this;
        $new->baseUrl = $baseUrl;
        return $new;
    }

    private bool $enableDefaultRoute = true;

    public function withEnableDefaultRoute(bool $enableDefaultRoute): self
    {
        $new = clone $this;
        $new->enableDefaultRoute = $enableDefaultRoute;
        return $new;
    }

    private ?Closure $rule = null;

    public function withRule(Closure $rule): self
    {
        $new = clone $this;
        $new->rule = $rule;
        return $new;
    }

    /**
     * @throws NotFoundException
     */
    public function match(string $path, string $method = Method::GET): array
    {
        return $this->solveRouteInfo(
            cachedDispatcher(function (RouteCollector $route): void {
                if ($this->rule) {
                    $route->addGroup($this->baseUrl, $this->rule);
                }
                // todo
                if ($routeCache = $this->annotate->getCache(AnnotationRoute::class)) {
                }
                if ($this->enableDefaultRoute) {
                    $route->addGroup($this->baseUrl, fn (RouteCollector $r) => $r->addRoute(...self::DEFAULT_ROUTE_RULE));
                }
            }, [
                'cacheFile' => $this->aliases->get($this->config->runtimeDir . '/route.cache'),
                'cacheDisabled' => $this->config->debug
            ])
                ->dispatch($method, rtrim($path, '/') ?: '/')
        );
    }

    private function solveRouteInfo(array $routeInfo): array
    {
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                return $this->replaceHandler($routeInfo[1], $routeInfo[2]);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return [false, null, null];
            default:
                throw new NotFoundException('Page is not found.');
        }
    }

    /**
     * @param string|array $handler
     */
    private function replaceHandler($handler, array $params): array
    {
        if (is_string($handler)) {
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
        }
        return [true, $handler, $params];
    }
}
