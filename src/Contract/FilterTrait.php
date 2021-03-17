<?php

declare(strict_types=1);

namespace Ep\Contract;

trait FilterTrait
{
    /**
     * @param  mixed $request
     * 
     * @return mixed
     */
    public function before($request)
    {
        return true;
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     */
    public function after($request, $response)
    {
        return $response;
    }

    private array $middlewares = [];

    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
