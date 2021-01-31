<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use RuntimeException;
use Ep\Base\Application as BaseApplication;
use Ep\Base\Router;
use Ep\Helper\Alias;
use Ep\Tests\App\web\Controller\IndexController;
use Ep\Tests\Cases\TestRouter;
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
        $response = $this->handleRequest($GLOBALS['argv']);
        $this->send($response);
        return 0;
    }

    protected function handleRequest($argv)
    {
        $n = new TestRouter();
        $n->testRules();
        die;

        unset($argv[0]);
        $path = '/' . array_shift($argv);
        $params = $argv;

        $router = new Router($path);
        [$handler] = $router->match();
        test($handler);
        // $controller = $router->createController($handler, $params);
    }
}
