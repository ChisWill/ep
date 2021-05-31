<?php

declare(strict_types=1);

namespace Ep\Web;

use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestHandlerFactory
{
    private ContainerInterface $container;
    private Injector $injector;
    private Service $service;

    public function __construct(ContainerInterface $container, Injector $injector, Service $service)
    {
        $this->container = $container;
        $this->injector = $injector;
        $this->service = $service;
    }

    public function wrap(array $middlewares, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        foreach ($this->buildMiddlewares($middlewares) as $middleware) {
            $handler = $this->wrapMiddleware($middleware, $handler);
        }
        return $handler;
    }

    public function create(callable $callback): RequestHandlerInterface
    {
        return new class($callback, $this->injector, $this->service) implements RequestHandlerInterface
        {
            private $callback;
            private Injector $injector;
            private Service $service;

            public function __construct(callable $callback, Injector $injector, Service $service)
            {
                $this->callback = $callback;
                $this->injector = $injector;
                $this->service = $service;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->service->toResponse($this->injector->invoke($this->callback, [$request]));
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
        return new class($callback, $this->injector, $this->service) implements MiddlewareInterface
        {
            private $callback;
            private Injector $injector;
            private Service $service;

            public function __construct(callable $callback, Injector $injector, Service $service)
            {
                $this->callback = $callback;
                $this->injector = $injector;
                $this->service = $service;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $this->service->toResponse($this->injector->invoke($this->callback, [$request, $handler]));
            }
        };
    }

    private function wrapMiddleware(MiddlewareInterface $middleware, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        return new class($middleware, $handler) implements RequestHandlerInterface
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
