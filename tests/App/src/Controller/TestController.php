<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Annotation\Aspect;
use Ep\Contract\ErrorRendererInterface;
use Ep\Db\Query;
use Ep\Helper\Str;
use Ep\Helper\System;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Service\TestService;
use Ep\Tests\Support\Container\AngelWing;
use Ep\Tests\Support\Container\Benz;
use Ep\Tests\Support\Container\BMW;
use Ep\Tests\Support\Container\CarInterface;
use Ep\Tests\Support\Container\DragoonEngine;
use Ep\Tests\Support\Container\EngineInterface;
use Ep\Tests\Support\Container\WingInterface;
use Ep\Tests\Support\Container\XEngine;
use Ep\Tests\Support\Middleware\CheckMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;
use Ep\Tests\Support\Middleware\MultipleMiddleware;
use Ep\Tests\Support\RequestHandler\ShowAttributeHandler;
use Ep\Web\RequestHandlerFactory;
use Ep\Web\ServerRequest;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Redis\Connection as RedisConnenct;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Container;
use Yiisoft\Strings\StringHelper;
use Ep\Annotation\Inject;
use Ep\Base\Container as BaseContainer;
use Ep\Contract\InjectorInterface;
use Ep\Tests\App\Aspect\ClassAnnotation;
use Ep\Tests\App\Aspect\EchoIntAspect;
use Ep\Tests\App\Aspect\EchoStringAspect;
use Ep\Tests\App\Aspect\LoggerAspect;
use Ep\Tests\App\Middleware\TimeMiddleware;
use Ep\Tests\App\Model\Student;
use Ep\Tests\App\Service\DemoService;
use Ep\Web\ErrorRenderer;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Injector\Injector;

/**
 * @ClassAnnotation
 */
class TestController extends Controller
{
    /**
     * @Inject(name="mary")
     */
    private TestService $service;
    /**
     * @Inject
     */
    private DemoService $demoService;

    /**
     * @Inject
     */
    private InjectorInterface $injector;

    public string $title = 'Test';

    private Connection $db;

    public function __construct()
    {
        $this->setMiddlewares([
            FilterMiddleware::class,
            MultipleMiddleware::class,
            TimeMiddleware::class
        ]);

        $this->db = Ep::getDb('sqlite');
    }

    public function before(ServerRequestInterface $request)
    {
        return true;
    }

    public function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    public function indexAction(ServerRequestInterface $serverRequest)
    {
        $message = 'test';

        return $this->render('/index/index', compact('message'));
    }

    public function injectAction()
    {
        return $this->json([
            'self' => $this->service->getAttr(),
            'demo' => $this->demoService->getAttr()
        ]);
    }

    public function tAction(ServerRequest $serverRequest)
    {
        return $this->success();
    }

    public function reduceAction(ServerRequest $serverRequest)
    {
        $n1 = 0;
        $r1 = Student::find($this->db)
            ->limit(2)
            ->select(['id', 'name', 'age'])
            ->reduce($n1, function ($data) {
                $ages = array_column($data, 'age');
                return array_sum($ages);
            });

        $n2 = 0;
        $r2 = Query::find($this->db)->from('student')->limit(2)->reduce($n2, function ($data) {
            $ages = array_column($data, 'age');
            return array_sum($ages);
        });

        return $this->json([$r1, $n1, $r2, $n2]);
    }

    /**
     * @LoggerAspect
     * @Aspect(class={EchoIntAspect::class, EchoStringAspect::class={"name"="pet","age"=10}})
     */
    public function aspectAction()
    {
        return $this->service->getRandom();
    }

    public function testUrlAction(ServerRequest $serverRequest)
    {
        return $this->json([
            $serverRequest->getCurrentUrl(),
            $serverRequest->getCurrentUrl('/test/shop/admin'),
            $serverRequest->getCurrentUrl('', ['a' => 1, 'b' => 'abc']),
            $serverRequest->getCurrentUrl('/test/shop/admin', ['a' => 1, 'b' => 'abc'])
        ]);
    }

    public function attrAction(ServerRequest $serverRequest)
    {
        $attributes = $serverRequest->getAttributes();

        return $this->json($attributes);
    }

    public function middleAction(ServerRequest $serverRequest)
    {
        $handler = Ep::getDi()->get(ShowAttributeHandler::class);
        return Ep::getDi()
            ->get(RequestHandlerFactory::class)
            ->wrap([
                CheckMiddleware::class,
                MultipleMiddleware::class
            ], $handler)
            ->handle($serverRequest);
    }

    public function diAction()
    {
        $composite = new CompositeContainer();
        $root1 = new Container([EngineInterface::class => XEngine::class]);
        $root2 = new Container([EngineInterface::class => DragoonEngine::class]);
        $root3 = new Container();
        $first = new Container([
            CarInterface::class => BMW::class,
            WingInterface::class => static fn (): AngelWing => new AngelWing(80),
        ], [], [], $root1);
        $second = new Container([
            CarInterface::class => Benz::class,
            WingInterface::class => AngelWing::class,
            'angelWing' => static fn (): AngelWing => new AngelWing(50),
        ], [], [], $root2);
        $third = new Container([
            WingInterface::class => AngelWing::class,
        ]);
        $composite->attach($first);
        $composite->attach($second);
        $composite->attach($third);


        return $this->json([
            'result' => $composite->get(AngelWing::class)
        ]);
    }

    public function mapAction()
    {
        $map = Student::find($this->db)
            ->joinWith('class')
            ->where('student.age > 10')
            ->map('class.id', 'student.age');

        return $this->json($map);
    }

    /**
     * @LoggerAspect
     * @Aspect(class=EchoIntAspect::class)
     */
    public function stringAction(Injector $injector)
    {
        return $this->service->getRandom();
    }

    public function arrayAction(ServerRequestInterface $request)
    {
        return $this->json([
            'state' => 1,
            'data' => [
                'msg' => 'ok'
            ],
            'query' => $request->getQueryParams(),
            'attributes' => $request->getAttributes()
        ]);
    }

    public function sqliteAction()
    {
        $result = Query::find($this->db)->from('user')->all();

        return $this->json($result);
    }

    public function errorAction(ServerRequestInterface $request)
    {
        $renderer = Ep::getDi()->get(ErrorRenderer::class);

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
