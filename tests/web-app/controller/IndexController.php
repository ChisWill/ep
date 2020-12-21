<?php

namespace webapp\controller;

use ep\Core;
use ep\web\Request;

class IndexController extends \ep\web\Controller
{
    public function index(Request $request)
    {
        tes(Core::getInstance()->getConfig());
        // $url = $request->createUrl('site/index', ['a' => 1]);

        // return $this->render();
        // return $this->render('index', compact('url'));
    }

    public function test(Request $request)
    {
    }
}
