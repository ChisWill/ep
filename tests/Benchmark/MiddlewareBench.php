<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

use Ep;
use Ep\Tests\Support\Middleware\AddMiddleware;
use Ep\Tests\Support\Middleware\CheckMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;
use Ep\Tests\Support\RequestHandler\FoundHandler;
use HttpSoft\Message\ServerRequestFactory;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @Iterations(5)
 * @Revs(1000)
 * @Groups({"base"})
 * @BeforeMethods({"before"})
 */
class MiddlewareBench
{
    private array $middlewareList = [
        CheckMiddleware::class,
        FilterMiddleware::class,
        AddMiddleware::class,
    ];

    public function before()
    {
        Ep::init(require(dirname(__DIR__, 1) . '/AppBasic/config/main.php'));
    }

    public function benchHandle()
    {
        $factory = new ServerRequestFactory;
        $serverRequest = $factory->createServerRequest('POST', '/user/list');
        $handler = Ep::getDi()->get(FoundHandler::class);
        $list = $this->createMiddle();
        foreach ($list as $middleware) {
            $handler = $this->wrap($middleware, $handler);
        }
        return $handler->handle($serverRequest);
    }

    public function benchNormal()
    {
        $factory = new ServerRequestFactory;
        $serverRequest = $factory->createServerRequest('POST', '/user/list');
        $handler = Ep::getDi()->get(FoundHandler::class);
        foreach ($this->middlewareList as $class) {
            /** @var MiddlewareInterface $middle */
            $middle = Ep::getDi()->get($class);
            $middle->process($serverRequest, $handler);
        }
    }

    public function benchNew()
    {
        foreach ($this->middlewareList as $class) {
            /** @var MiddlewareInterface $middle */
            $middle = Ep::getDi()->get($class);
        }
    }

    private function createMiddle()
    {
        $list = [];
        foreach ($this->middlewareList as $class) {
            $list[] = Ep::getDi()->get($class);
        }
        return $list;
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
