<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use RuntimeException;
use Ep\Base\Application as BaseApplication;
use Ep\Base\Router;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Injector\Injector;

class Application extends BaseApplication
{
    protected function handle(): int
    {
        $response = $this->handleRequest($this->createRequest());
        $this->send($response);
        return 0;
    }

    protected function send(ResponseInterface $response): void
    {
    }

    protected function createRequest(): ServerRequestInterface
    {
        return Ep::getDi()->get(ServerRequestFactory::class)->createFromGlobals();
    }

    protected function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();
        $router = new Router(Ep::getConfig()->getRouteRules());
        $path = $router->match($uri->getPath());
        [$controllerName, $actionName] = $router->getControllerActionName($path);
        if (!class_exists($controllerName)) {
            throw new RuntimeException("{$controllerName} is not found.");
        }
        $controller = new $controllerName;
        if (!method_exists($controller, $actionName)) {
            throw new RuntimeException("{$actionName} is not found.");
        }
        return call_user_func([$controller, $actionName], $request);
    }
}
