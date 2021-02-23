<?php

declare(strict_types=1);

namespace Ep\Web;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    private ServerRequestFactoryInterface $serverRequestFactory;

    public function __construct(ServerRequestFactoryInterface $serverRequestFactory)
    {
        $this->serverRequestFactory = $serverRequestFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($this->serverRequestFactory->createServerRequest($method, $uri, $serverParams));
    }
}
