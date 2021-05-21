<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Contract\ContextTrait;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ControllerInterface;
use Ep\Contract\FilterTrait;
use LogicException;

abstract class Command implements ControllerInterface
{
    use ContextTrait, FilterTrait;

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

    private ?Service $service = null;

    protected function getService(): Service
    {
        if ($this->service === null) {
            $this->service = Ep::getDi()->get(Service::class);
        }
        return $this->service;
    }

    protected function alias(): array
    {
        return [];
    }

    /**
     * @throws LogicException
     */
    public function setMiddlewares(array $middlewares): void
    {
        throw new LogicException('Console command doesn\'t have middlewares yet.');
    }

    /**
     * @throws LogicException
     */
    public function getMiddlewares(): array
    {
        throw new LogicException('Console command doesn\'t have middlewares yet.');
    }
}
