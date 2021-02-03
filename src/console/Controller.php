<?php

namespace Ep\Console;

use Ep\base\Controller as BaseController;
use Ep\Standard\ResponseHandlerInterface;

class Controller extends BaseController
{
    public function createResponseHandler(): ResponseHandlerInterface
    {
        return new ResponseHandler;
    }

    public function beforeAction(): bool
    {
        return true;
    }

    public function afterAction(ResponseHandlerInterface $responseHandler): void
    {
    }
}
