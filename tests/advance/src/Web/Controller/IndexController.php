<?php

namespace Ep\Tests\Advance\Web\Controller;

use Ep\Tests\Advance\Common\Component\Controller;
use Yiisoft\Http\Status;

class IndexController extends Controller
{
    public string $title = 'Advance 首页';

    public function __construct()
    {
        $this->getView()->layout = 'main';
    }

    public function indexAction()
    {
        $message = 'This is advance index.';

        return $this->render('index', compact('message'));
    }

    public function missAction()
    {
        return $this->string('迷路的很高级', Status::NOT_FOUND);
    }

    public function errorAction()
    {
        return $this->string('错的很严重');
    }
}
