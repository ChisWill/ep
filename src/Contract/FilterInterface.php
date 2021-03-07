<?php

declare(strict_types=1);

namespace Ep\Contract;

interface FilterInterface
{
    /**
     * @param  mixed $request
     * 
     * @return mixed
     */
    public function before($request);

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     */
    public function after($request, $response);

    public function setMiddlewares(array $middlewares): void;

    public function getMiddlewares(): array;
}
