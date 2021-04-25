<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep;
use Ep\Contract\ErrorRendererInterface;
use Ep\Db\Query;
use Ep\Helper\Str;
use Ep\Helper\System;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Model\User;
use Ep\Tests\App\Model\UserParent;
use Ep\Tests\Support\Container\AngelWing;
use Ep\Tests\Support\Container\Benz;
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
use Ep\Tests\Support\Middleware\MultipleMiddleware;
use Ep\Tests\Support\RequestHandler\ShowAttributeHandler;
use Ep\Web\ErrorHandler;
use Ep\Web\ErrorRenderer;
use Ep\Web\RequestHandlerFactory;
use Ep\Web\ServerRequest;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Redis\Connection;
use Yiisoft\Di\CompositeContainer;
use Yiisoft\Di\Container;
use Yiisoft\Strings\StringHelper;

class TestController extends Controller
{
    public string $title = 'Test';

    public function __construct()
    {
        $this->setMiddlewares([
            FilterMiddleware::class,
            MultipleMiddleware::class
        ]);
    }

    public function before($request)
    {
        return true;
    }

    public function indexAction(ServerRequestInterface $serverRequest)
    {
        $message = 'test';

        return $this->render('/index/index', compact('message'));
    }

    public function tAction(ServerRequest $serverRequest)
    {
    }

    public function testUrlAction(ServerRequest $serverRequest)
    {
        return [
            $serverRequest->getCurrentUrl(),
            $serverRequest->getCurrentUrl('/test/shop/admin'),
            $serverRequest->getCurrentUrl('', ['a' => 1, 'b' => 'abc']),
            $serverRequest->getCurrentUrl('/test/shop/admin', ['a' => 1, 'b' => 'abc'])
        ];
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
            WingInterface::class => static fn () => new AngelWing(80),
        ], [], [], $root1);
        $second = new Container([
            CarInterface::class => Benz::class,
            WingInterface::class => AngelWing::class,
            'angelWing' => static fn () => new AngelWing(50),
        ], [], [], $root2);
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

    public function mapAction()
    {
        return User::find()
            ->joinWith('parent')
            ->where('user.age > 100')
            ->map('parent.id', 'user.age');
    }

    public function lockAction(Connection $redis)
    {
        $r = $this->lock(function () {
            Query::find()->update('user', ['age' => new Expression('age+1')], ['AND', ['id' => 2], ['<', 'age', 100]]);
        }, 1000);

        return [
            'r' => $r
        ];
    }

    private function lock(callable $callback, int $expire = 1, int $count = 1)
    {
        /** @var Connection */
        $redis = Ep::getDi()->get(Connection::class);

        $key = self::class . System::getCallerName();
        $value = Str::random();
        $times = 10;
        do {
            $ok = $redis->set($key, $value, 'NX', 'PX', $expire * 1000);
            if ($ok) {
                $callback();
                $script = <<<SCRIPT
    if redis.call('get', KEYS[1]) == ARGV[1] then 
        return redis.call('del', KEYS[1])
    else
        return 0
    end
    SCRIPT;
                $redis->eval($script, 1, $key, $value);
            } else {
                $times--;
                usleep(50 * 1000);
            }
        } while (!$ok && $times > 0);

        return !!$ok;
    }

    public function emptyAction()
    {
    }

    public function stringAction()
    {
        return 'test string';
    }

    public function arrayAction(ServerRequestInterface $request)
    {
        return [
            'state' => 1,
            'data' => [
                'msg' => 'ok'
            ],
            'query' => $request->getQueryParams(),
            'attributes' => $request->getAttributes()
        ];
    }

    public function sqliteAction()
    {
        $db = Ep::getDb('sqlite');

        return Query::find($db)->from('user')->all();
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
