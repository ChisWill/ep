<?php

declare(strict_types=1);

namespace Ep\Tests\Support\RequestHandler;

use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorHandler implements RequestHandlerInterface
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->service->json(
            [
                'query' => $request->getQueryParams(),
                'error' => $request->getParsedBody()
            ]
        );
    }
}
