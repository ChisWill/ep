<?php

namespace Ep\Tests\Basic\Controller;

use Ep;
use Ep\Tests\Basic\Component\Controller;
use Ep\Web\ErrorHandler;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class TestController extends Controller
{
    public string $title = 'Test';

    public function indexAction(ServerRequestInterface $req)
    {
        $message = 'test';

        return $this->render('/index/index', compact('message'));
    }

    public function sleepAction()
    {
        sleep(5);

        return 'ok';
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
        $handler = Ep::getDi()->get(ErrorHandler::class);

        return $this->string($handler->renderException(
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
