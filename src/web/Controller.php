<?php

namespace Ep\Web;

use Ep\base\Controller as BaseController;

class Controller extends BaseController
{
    public function beforeAction(): bool
    {
        return true;
    }

    public function afterAction($response): void
    {
    }
}
