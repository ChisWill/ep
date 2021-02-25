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
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\Middleware\Dispatcher\MiddlewareFactory;
use Yiisoft\Middleware\Dispatcher\MiddlewareStack;

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

    private ServerRequestInterface $request;

    public function before()
    {
        Ep::init(require(dirname(__DIR__, 1) . '/AppBasic/config/main.php'));

        $factory = new ServerRequestFactory;
        $this->request = $factory->createServerRequest('POST', '/user/list');
    }

    public function benchNormal()
    {
        $handler = Ep::getDi()->get(FoundHandler::class);
        $list = $this->createByNormal();
        foreach ($list as $middleware) {
            $handler = $this->wrap($middleware, $handler);
        }
        return $handler->handle($this->request);
    }

    public function benchYii()
    {
        $handler = Ep::getDi()->get(FoundHandler::class);
        $list = $this->createByYii();
        foreach ($list as $middleware) {
            $handler = $this->wrap($middleware, $handler);
        }
        return $handler->handle($this->request);
    }

    public function benchFullYii()
    {
        $handler = Ep::getDi()->get(FoundHandler::class);

        $factory = new MiddlewareFactory(Ep::getDi());
        $stack = new MiddlewareStack(Ep::getEventDispatcher());
        $dispatcher = new MiddlewareDispatcher($factory, $stack);
        $dispatcher = $dispatcher->withMiddlewares($this->middlewareList);
        return $dispatcher->dispatch($this->request, $handler);
    }

    private function createByNormal()
    {
        $list = [];
        foreach ($this->middlewareList as $class) {
            $list[] = Ep::getDi()->get($class);
        }
        return $list;
    }

    private function createByYii()
    {
        $list = [];
        $factory = new MiddlewareFactory(Ep::getDi());
        foreach ($this->middlewareList as $class) {
            $list[] = $factory->create($class);
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
