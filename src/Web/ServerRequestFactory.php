<?php

declare(strict_types=1);

namespace Ep\Web;

use HttpSoft\Message\ServerRequestFactory as BaseRequestFactory;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest((new BaseRequestFactory)->createServerRequest($method, $uri, $serverParams));
    }
}
