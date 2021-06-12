<?php

declare(strict_types=1);

namespace Ep\Tests\App\Middleware;

use Ep\Web\Service as WebService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ep\Annotation\Aspect;
use Ep\Annotation\Service;

final class TimeMiddleware implements MiddlewareInterface
{
    /**
     * @Service
     */
    private WebService $service;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);

        $response = $handler->handle($request);

        $response->getBody()->write('<br>' . (microtime(true) - $start) * 1000 . 'ms');

        return $response;
    }
}
