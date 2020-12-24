<?php

namespace webapp\controller;

use ep\web\Request;
use ep\web\Response;

class IndexController extends \ep\web\Controller
{
    public function index(Request $request, Response $response)
    {
        return $response->render();
    }
}
