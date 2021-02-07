<?php

namespace Ep\Console;

use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

class Controller extends \Ep\base\Controller
{
    public function createResponseHandler(): ResponseInterface
    {
        return new Response();
    }

    public function beforeAction($request): bool
    {
        return true;
    }

    public function afterAction($response): bool
    {
        return true;
    }
}
