<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Annotation\Route;
use Ep\Tests\App\Component\Controller;

class StateController extends Controller
{
    /**
     * @Route("state/ping")
     */
    public function pingAction()
    {
        return $this->string('pong');
    }
}
