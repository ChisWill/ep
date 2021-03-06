<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep\Base\ControllerFactory;
use Ep\Base\Route;
use Ep\Contract\NotFoundException;
use Ep\Web\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouteMiddleware implements MiddlewareInterface
{
    private Route $route;
    private ControllerFactory $controllerFactory;
    private Service $service;

    public function __construct(Route $route, ControllerFactory $controllerFactory, Service $service)
    {
        $this->route = $route;
        $this->controllerFactory = $controllerFactory;
        $this->service = $service;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            [$result, $params] = $this->route->match(
                $request->getUri()->getPath(),
                $request->getMethod()
            );

            foreach ($params as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }

            return $this->service->toResponse($this->controllerFactory->run($result, $request));
        } catch (NotFoundException $e) {
            return $handler->handle($request);
        }
    }
}
