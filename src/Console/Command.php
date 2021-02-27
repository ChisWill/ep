<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ContextTrait;
use Ep\Base\View;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ControllerInterface;

abstract class Command implements ControllerInterface
{
    use ContextTrait;

    /**
     * @param  ConsoleRequestInterface $request
     * 
     * @return mixed
     */
    public function before($request)
    {
        return true;
    }

    /**
     * @param  ConsoleRequestInterface $request 
     * @param  mixed                   $response
     * 
     * @return mixed
     */
    public function after($request, $response)
    {
        return $response;
    }

    public function getViewPath(): string
    {
        return Ep::getConfig()->viewPath;
    }
}
