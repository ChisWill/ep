<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Standard\RouteInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application extends \Ep\Base\Application
{
    protected function handle(): void
    {
        $response = $this->handleRequest($this->createRequest());
        if ($response instanceof ResponseInterface) {
        }
    }

    protected function createRequest(): ServerRequestInterface
    {
        return Ep::getDi()->get(ServerRequestFactory::class)->createFromGlobals();
    }

    protected function handleRequest(ServerRequestInterface $request): ?ResponseInterface
    {
        $route = Ep::getDi()->get(RouteInterface::class);
        [$handler, $params] = $route->solveRouteInfo($route->matchRequest($request->getUri()->getPath(), $request->getMethod()));
        if ($params) {
            $request = $request->withQueryParams($params);
        }
        [$controllerClass, $actionName] = $route->parseHandler($handler);
        return $route->createController($controllerClass)->run($actionName, $request);
    }
}
