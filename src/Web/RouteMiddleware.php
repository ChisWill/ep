<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\ControllerFactory;
use Ep\Base\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UnexpectedValueException;

class RouteMiddleware implements MiddlewareInterface
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

    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        [$handler, $params] = $this->route->match(
            $request->getUri()->getPath(),
            $request->getMethod()
        );

        foreach ($params as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        try {
            return $this->service->toResponse($this->controllerFactory->run($handler, $request));
        } catch (UnexpectedValueException $e) {
            return $requestHandler->handle($request);
        }
    }
}
