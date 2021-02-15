<?php

declare(strict_types=1);

namespace Ep\Contract;

use Psr\Http\Message\ServerRequestInterface;

interface ServerRequestFactoryInterface extends \Psr\Http\Message\ServerRequestFactoryInterface
{
    public function createFromGlobals(): ServerRequestInterface;

    public function createFromParameters(array $server, array $headers = [], array $cookies = [], array $get = [], array $post = [], array $files = [], $body = null): ServerRequestInterface;
}
