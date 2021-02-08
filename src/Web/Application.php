<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Standard\RouteInterface;
use Ep\Standard\ServerRequestFactoryInterface;
use Yiisoft\Http\Method;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application extends \Ep\Base\Application
{
    protected function handle(): void
    {
        $request = $this->createRequest();
        $response = $this->handleRequest($request);
        if ($response instanceof ResponseInterface) {
            $emitter = new SapiEmitter();
            $emitter->emit($response, $request->getMethod() === Method::HEAD);
        }
    }

    protected function createRequest(): ServerRequestInterface
    {
        return Ep::getDi()
            ->get(ServerRequestFactoryInterface::class)
            ->createFromGlobals();
    }

    protected function handleRequest(ServerRequestInterface $request): ?ResponseInterface
    {
        $route = Ep::getDi()->get(RouteInterface::class);
        [$handler, $params] = $route->solveRouteInfo($route->matchRequest($request->getUri()->getPath(), $request->getMethod()));
        $request = $request->withQueryParams($params);
        [$controllerClass, $actionName] = $route->parseHandler($handler);
        return $route->createController($controllerClass)
            ->run($actionName, $request);
    }
}
