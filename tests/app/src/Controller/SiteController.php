<?php

namespace Ep\Tests\App\Controller;

use Ep\Tests\App\Common\Component\Controller;

class SiteController extends Controller
{
    public function indexAction()
    {
        $message = 'Welcome';

        return $this->render('index', compact('message'));
    }

    public function userInfoAction()
    {
        echo 'info';
    }
}
