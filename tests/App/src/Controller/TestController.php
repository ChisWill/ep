<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Contract\ErrorRendererInterface;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\Support\Container\AngelWing;
use Ep\Tests\Support\Container\Benz;
use Ep\Tests\Support\Container\Bird;
use Ep\Tests\Support\Container\BMW;
use Ep\Tests\Support\Container\CarInterface;
use Ep\Tests\Support\Container\DragoonEngine;
use Ep\Tests\Support\Container\EngineInterface;
use Ep\Tests\Support\Container\MegaBird;
use Ep\Tests\Support\Container\WingInterface;
use Ep\Tests\Support\Container\XEngine;
use Ep\Tests\Support\Middleware\AddMiddleware;
use Ep\Tests\Support\Middleware\CheckMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;
use Ep\Tests\Support\Middleware\InitMiddleware;
use Ep\Tests\Support\RequestHandler\FoundHandler;
use Ep\Tests\Support\RequestHandler\ShowAttributeHandler;
use Ep\Web\ErrorHandler;
use Ep\Web\ErrorRenderer;
use Ep\Web\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Container;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;

class TestController extends Controller
{
    public string $title = 'Test';

    public function indexAction(ServerRequestInterface $req)
    {
        $message = 'test';

        return $this->render('/index/index', compact('message'));
    }

    public function middleAction(ServerRequest $serverRequest)
    {
        $dispatcher = Ep::getDi()->get(MiddlewareDispatcher::class);

        $dispatcher = $dispatcher->withMiddlewares([
            CheckMiddleware::class,
            AddMiddleware::class,
            InitMiddleware::class
        ]);

        return $dispatcher->dispatch($serverRequest, Ep::getDi()->get(ShowAttributeHandler::class));
    }

    public function diAction()
    {
        $composite = new CompositeContainer();
        $root1 = new Container([EngineInterface::class => XEngine::class]);
        $root2 = new Container([EngineInterface::class => DragoonEngine::class]);
        $root3 = new Container();
        $first = new Container([
            CarInterface::class => BMW::class,
            WingInterface::class => static fn () => new AngelWing(80),
        ], [], $root1);
        $second = new Container([
            CarInterface::class => Benz::class,
            WingInterface::class => AngelWing::class,
            'angelWing' => static fn () => new AngelWing(50),
        ], [], $root2);
        $third = new Container([
            WingInterface::class => AngelWing::class,
        ]);
        $composite->attach($first);
        $composite->attach($second);
        $composite->attach($third);


        return [
            'result' => $composite->get(MegaBird::class)
        ];
    }

    public function stringAction()
    {
        return 'test string';
    }

    public function arrayAction()
    {
        return [
            'state' => 1,
            'data' => [
                'msg' => 'ok'
            ]
        ];
    }

    public function errorAction(ServerRequestInterface $request)
    {
        $renderer = Ep::getDi()->get(ErrorRendererInterface::class);

        return $this->string($renderer->render(
            new RuntimeException(
                '我错了',
                500,
                new RuntimeException(
                    "我又错啦",
                    600,
                    new RuntimeException(
                        "我怎么总错",
                        700
                    )
                ),
            ),
            $request
        ));
    }
}
