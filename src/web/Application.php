<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use RuntimeException;
use Ep\Base\Application as BaseApplication;
use Ep\Base\Router;
use Ep\Helper\Alias;
use Ep\Tests\App\web\Controller\IndexController;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Injector\Injector;

use function FastRoute\cachedDispatcher;
use function FastRoute\simpleDispatcher;

class Application extends BaseApplication
{
    protected function handle(): int
    {
        $request = $this->createRequest();
        $response = $this->handleRequest($request);
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

    protected function handleRequest(ServerRequestInterface &$request)
    {
        $router = new Router();
        [$handler, $params] = $router->solveRouteInfo($router->match($request->getUri()->getPath(), $request->getMethod()));
        if ($params) {
            $request = $request->withQueryParams($params);
        }
        [$controllerClass, $actionName] = $router->parseHandler($handler);
        $controller = $this->createController($controllerClass);
        return $this->runAction($controllerClass, $actionName);
    }
}
