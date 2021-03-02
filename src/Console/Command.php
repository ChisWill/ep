<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ContextTrait;
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
        $request->setAlias($this->alias());

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

    protected function alias(): array
    {
        return [];
    }

    public function getViewPath(): string
    {
        return Ep::getConfig()->viewPath;
    }
}
