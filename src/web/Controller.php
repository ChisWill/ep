<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Controller as BaseController;
use Ep\Base\ResponseHandlerInterface;

class Controller extends BaseController
{
    protected function createResponseHandler(): ResponseHandlerInterface
    {
        return new View($this);
    }

    protected function beforeAction(): bool
    {
        return true;
    }

    protected function afterAction(ResponseHandlerInterface $responseHandler): void
    {
    }

    protected function jsonSuccess()
    {
    }

    protected function jsonError()
    {
    }

    protected function redirect()
    {
    }
}
