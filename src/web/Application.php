<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Standard\RouteInterface;
use Ep\Standard\ResponseHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application extends \Ep\Base\Application
{
    protected function handle(): void
    {
        $this->handleRequest($this->createRequest())->send();
    }

    protected function createRequest(): ServerRequestInterface
    {
        return Ep::getDi()->get(ServerRequestFactory::class)->createFromGlobals();
    }

    protected function handleRequest(ServerRequestInterface $request): ResponseHandlerInterface
    {
        $route = Ep::getDi()->get(RouteInterface::class);
        [$handler, $params] = $route->solveRouteInfo($route->matchRule($request->getUri()->getPath(), $request->getMethod()));
        if ($params) {
            $request = $request->withQueryParams($params);
        }
        [$controllerClass, $actionName] = $route->parseHandler($handler);
        return $this->createController($controllerClass)->run($actionName, $request);
    }
}
