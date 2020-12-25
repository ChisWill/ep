<?php

namespace ep\web;

use ep\base\Controller as BaseController;

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
