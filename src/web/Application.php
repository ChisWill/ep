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
        $router = new Router($request->getUri()->getPath(), $request->getMethod());
        [$handler, $params] = $router->match();
        $request = $request->withQueryParams($params);
        test($handler);
        // $controller = $router->createController($handler, $params);
    }
}
