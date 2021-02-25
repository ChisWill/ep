<?php

declare(strict_types=1);

namespace Ep\Contract;

use Psr\Http\Message\ServerRequestInterface;

interface ControllerInterface extends ContextInterface
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
}