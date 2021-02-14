<?php

namespace Ep\Tests\Basic\Controller;

use Ep;
use Ep\Helper\Arr;
use Ep\Tests\Basic\Component\Controller;
use Ep\Web\ErrorHandler;
use RuntimeException;

class TestController extends Controller
{
    public function indexAction()
    {
        $a = [4];
        $r = Arr::getValue($a, 3, 9);
        test($r);
    }

    public function errorAction()
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
            )
        ));
    }
}
