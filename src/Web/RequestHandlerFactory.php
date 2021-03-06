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
    private Service $service;

    public function __construct(ContainerInterface $container, Service $service)
    {
        $this->container = $container;
        $this->service = $service;
    }

    public function wrap(array $middlewares, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        foreach ($this->buildMiddlewares($middlewares) as $middleware) {
            $handler = $this->wrapRequestHandler($middleware, $handler);
        }
        return $handler;
    }

    private function buildMiddlewares(array $middlewares): iterable
    {
        foreach ($middlewares as $definition) {
            if (is_string($definition)) {
                yield $this->container->get($definition);
            } elseif (is_callable($definition)) {
                yield $this->wrapMiddleware($definition);
            }
        }
    }

    private function wrapMiddleware($callback): MiddlewareInterface
    {
        return new class($callback, $this->container, $this->service) implements MiddlewareInterface
        {
            private $callback;
            private ContainerInterface $container;
            private Service $service;

            public function __construct(callable $callback, ContainerInterface $container, Service $service)
            {
                $this->callback = $callback;
                $this->container = $container;
                $this->service = $service;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $this->service->toResponse((new Injector($this->container))->invoke($this->callback, [$request, $handler]));
            }
        };
    }

    private function wrapRequestHandler(MiddlewareInterface $middleware, RequestHandlerInterface $handler): RequestHandlerInterface
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
