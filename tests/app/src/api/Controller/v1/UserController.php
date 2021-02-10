<?php

namespace Ep\Tests\App\Api\Controller\V1;

use Ep\Tests\App\Common\Component\Controller;

class UserController extends Controller
{
    public function indexAction()
    {
        return $this->success();
    }
}
