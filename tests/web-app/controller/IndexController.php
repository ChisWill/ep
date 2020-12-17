<?php

namespace webapp\controller;

use ep\web\Request;

class IndexController extends \ep\web\Controller
{
    public function index(Request $request)
    {
        $r = $request->getQueryParams();
        tes($r);
    }

    public function cat(Request $request)
    {
        $r = $request->getQueryParams();
        tes($r);
    }

    public function login(Request $request)
    {
        tes($request->getQueryParams()['ids']);
        test('i login');
    }

    public function single()
    {
        test('i single');
    }
}
