<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Tests\App\Component\Controller;

class StateController extends Controller
{
    public function pingAction()
    {
        return $this->string('pong');
    }
}
