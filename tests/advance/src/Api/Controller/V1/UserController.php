<?php

namespace Ep\Tests\Advance\Api\Controller\V1;

use Ep\Tests\Advance\Common\Component\Controller;

class UserController extends Controller
{
    public function listAction()
    {
        return $this->success(['v1']);
    }
}
