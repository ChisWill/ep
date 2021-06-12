<?php

declare(strict_types=1);

namespace Ep\Contract;

/**
 * @method mixed before($request)
 * @method mixed after($request, $response)
 */
interface FilterInterface
{
    public function setMiddlewares(array $middlewares): void;

    public function getMiddlewares(): array;
}
