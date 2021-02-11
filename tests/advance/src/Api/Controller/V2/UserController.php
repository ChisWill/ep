<?php

namespace Ep\Tests\Advance\Api\Controller\V2;

use Ep\Tests\Advance\Common\Component\Controller;

class UserController extends Controller
{
    public function listAction()
    {
        return $this->success(['v2']);
    }
}
