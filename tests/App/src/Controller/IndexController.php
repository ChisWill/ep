<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Tests\App\Component\Controller;
use Ep\Web\ServerRequest;

class IndexController extends Controller
{
    public string $title = '首页';

    public function indexAction(ServerRequest $serverRequest)
    {
        $message = 'Default Page';

        return $this->render('index', compact('message'));
    }

    public function errorAction()
    {
        return $this->string('我错了');
    }
}
