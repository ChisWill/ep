<?php

namespace Ep\Tests\Basic\Controller;

use Ep\Tests\Basic\Component\Controller;

class StateController extends Controller
{
    public function pingAction()
    {
        return $this->string('pong');
    }
}
