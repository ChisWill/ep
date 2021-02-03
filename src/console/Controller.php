<?php

namespace Ep\Console;

use Ep\Standard\ResponseHandlerInterface;

class Controller extends \Ep\base\Controller
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
