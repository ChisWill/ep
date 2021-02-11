<?php

namespace Ep\Tests\Basic\Controller;

use Ep\Tests\Basic\Component\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $message = 'Default Page';

        return $this->render('index', compact('message'));
    }
}
