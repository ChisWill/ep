<?php

declare(strict_types=1);

namespace Ep\Annotation;

use FastRoute\RouteCollector;
use Closure;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Route extends Prepare
{
    private array $routes = [];

    public function __construct(array $values)
    {
        // todo
        // tt($values);
    }

    public function prepare(): void
    {
    }

    public function getRouteRule(): Closure
    {
        return function (RouteCollector $route) {
            foreach ($this->routes as $path => $handler) {
                $route->addRoute(['GET', 'POST'], $path, $handler);
            }
        };
    }
}
