<?php

declare(strict_types=1);

namespace Ep\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Middleware\Dispatcher\MiddlewareStackInterface;
use RuntimeException;

final class MiddlewareStack implements MiddlewareStackInterface
{
    /**
     * @var RequestHandlerInterface|null $stack
     */
    private ?RequestHandlerInterface $stack = null;

    public function build(array $middlewares, RequestHandlerInterface $handler): MiddlewareStackInterface
    {
        foreach ($middlewares as $middleware) {
            $handler = $this->wrap($middleware, $handler);
        }
        $new = clone $this;
        $new->stack = $handler;
        return $new;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('Stack is empty.');
        }
        return $this->stack->handle($request);
    }

    public function reset(): void
    {
        $this->stack = null;
    }

    public function isEmpty(): bool
    {
        return $this->stack === null;
    }

    private function wrap(MiddlewareInterface $middleware, RequestHandlerInterface $handler): RequestHandlerInterface
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
