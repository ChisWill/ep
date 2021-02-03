<?php

namespace Ep\Tests\App\api\Controller;

class UserController extends \Ep\Web\Controller
{
    public function listAction()
    {
        return $this->renderPartial('a');
    }
}
