<?php

namespace Ep\Tests\Basic\Controller;

use Ep;
use Ep\Tests\Basic\Component\Controller;
use Ep\Web\ErrorHandler;
use Exception;
use RuntimeException;
use Yiisoft\Http\Status;

class IndexController extends Controller
{
    public string $name = 'idx';

    public function indexAction()
    {
        $message = 'Default Page';

        return $this->render('index', compact('message'));
    }

    public function missAction()
    {
        return $this->string('迷路了', Status::NOT_FOUND);
    }

    public function errorAction()
    {
        return $this->string('我错了');
    }

    public function testErrorAction()
    {
        $handler = Ep::getDi()->get(ErrorHandler::class);

        return $this->string($handler->renderException(
            new RuntimeException(
                '我错了',
                500,
                new \Yiisoft\Db\Exception\Exception(
                    "我又错啦",
                    [],
                    new \Yiisoft\Db\Exception\Exception(
                        "我怎么总错",
                    )
                ),
            )
        ));
    }
}
