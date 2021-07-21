<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

use Ep;
use Ep\Tests\Support\Middleware\AddMiddleware;
use Ep\Tests\Support\Middleware\CheckMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;
use Ep\Tests\Support\Middleware\InitMiddleware;
use Ep\Tests\Support\RequestHandler\NotFoundHandler;
use Ep\Web\RequestHandlerFactory;
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

/**
 * @Iterations(5)
 * @Revs(100)
 * @Groups({"base"})
 * @BeforeMethods({"before"})
 */
class MiddlewareBench
{
    private array $middlewareList = [
        CheckMiddleware::class,
        FilterMiddleware::class,
        AddMiddleware::class,
        InitMiddleware::class,
    ];

    private ServerRequestInterface $request;

    public function before()
    {
        Ep::init(require(dirname(__DIR__, 1) . '/App/config/main.php'));

        $factory = new ServerRequestFactory;
        $this->request = $factory->createServerRequest('POST', '/user/list');
    }

    public function benchSelf()
    {
        $handler = Ep::getDi()->get(NotFoundHandler::class);
        return Ep::getDi()
            ->get(RequestHandlerFactory::class)
            ->wrap($this->middlewareList, $handler)
            ->handle($this->request);
    }

    public function benchYii()
    {
        $handler = Ep::getDi()->get(NotFoundHandler::class);
        $list = $this->createByYii();
        foreach ($list as $middleware) {
            $handler = $this->wrap($middleware, $handler);
        }
        return $handler->handle($this->request);
    }

    public function benchFullYii()
    {
        $handler = Ep::getDi()->get(NotFoundHandler::class);
        return Ep::getDi()
            ->get(MiddlewareDispatcher::class)
            ->withMiddlewares($this->middlewareList)
            ->dispatch($this->request, $handler);
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
