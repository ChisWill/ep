<?php

namespace Ep\Tests\Basic\Controller;

use Ep;
use Ep\Tests\Basic\Component\Controller;
use Ep\Web\ErrorHandler;
use RuntimeException;

class IndexController extends Controller
{
    public string $name = 'idx';

    public function indexAction()
    {
        $message = 'Default Page';

        return $this->render('index', compact('message'));
    }

    public function errorAction()
    {
        $handler = Ep::getDi()->get(ErrorHandler::class);

        return $handler->renderException(
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
        );
    }
}
