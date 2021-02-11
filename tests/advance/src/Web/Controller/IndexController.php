<?php

namespace Ep\Tests\Advance\Web\Controller;

use Ep\Tests\Advance\Common\Component\Controller;

class IndexController extends Controller
{
    public function __construct()
    {
        $this->getView()->layout = 'main';
    }

    public function indexAction()
    {
        $message = 'This is advance index.';

        return $this->render('index', compact('message'));
    }
}
