<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Middleware;

use Ep;
use Ep\Tests\Support\RequestHandler\Handler;
use Ep\Web\RequestHandlerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FilterMiddleware implements MiddlewareInterface
{
    private RequestHandlerFactory $factory;

    public function __construct(RequestHandlerFactory $factory)
    {
        $this->factory = $factory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $controller = Ep::getDi()->get(Handler::class);

        $middlewareDefinitions = [
            [$controller, 'do'],
        ];

        $handler = $this->factory->wrap($middlewareDefinitions, $handler);

        return $handler->handle($request);
    }
}
