<?php

namespace Ep\Tests\Basic\Controller;

use Ep\Tests\Basic\Component\Controller;
use Yiisoft\Http\Status;

class IndexController extends Controller
{
    public string $title = '首页';

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
}
