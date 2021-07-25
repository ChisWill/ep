<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Contract\InjectorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestHandlerFactory
{
    private ContainerInterface $container;
    private InjectorInterface $injector;

    public function __construct(ContainerInterface $container, InjectorInterface $injector)
    {
        $this->container = $container;
        $this->injector = $injector;
    }

    public function wrap(array $middlewares, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        krsort($middlewares);
        foreach ($this->buildMiddlewares($middlewares) as $middleware) {
            $handler = $this->wrapMiddleware($middleware, $handler);
        }
        return $handler;
    }

    public function create(callable $callback): RequestHandlerInterface
    {
        return new class ($callback, $this->injector) implements RequestHandlerInterface
        {
            private $callback;
            private InjectorInterface $injector;

            public function __construct(callable $callback, InjectorInterface $injector)
            {
                $this->callback = $callback;
                $this->injector = $injector;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->injector->invoke($this->callback, [$request]);
            }
        };
    }

    private function buildMiddlewares(array $middlewares): iterable
    {
        foreach ($middlewares as $definition) {
            if (is_string($definition)) {
                yield $this->container->get($definition);
            } elseif (is_callable($definition)) {
                yield $this->wrapCallback($definition);
            }
        }
    }

    private function wrapCallback(callable $callback): MiddlewareInterface
    {
        return new class ($callback, $this->injector) implements MiddlewareInterface
        {
            private $callback;
            private InjectorInterface $injector;

            public function __construct(callable $callback, InjectorInterface $injector)
            {
                $this->callback = $callback;
                $this->injector = $injector;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $this->injector->invoke($this->callback, [$request, $handler]);
            }
        };
    }

    private function wrapMiddleware(MiddlewareInterface $middleware, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        return new class ($middleware, $handler) implements RequestHandlerInterface
        {
            private MiddlewareInterface $middleware;
            private RequestHandlerInterface $handler;

            public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $handler)
            {
                $this->middleware = $middleware;
                $this->handler = $handler;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->middleware->process($request, $this->handler);
            }
        };
    }
}
