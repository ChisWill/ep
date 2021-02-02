<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\Application as BaseApplication;
use Ep\Base\Route;

class Application extends BaseApplication
{
    protected function handle(): void
    {
        $response = $this->handleRequest($GLOBALS['argv']);
        $response->send();
    }

    protected function handleRequest($argv): ResponseHandler
    {
        unset($argv[0]);
        $path = '/' . array_shift($argv);
        $params = $argv;

        $route = new Route($path);
        // [$handler] = $route->match();
        // test($handler);
        $controller = $this->createController('');
        return $controller->run('', '');
    }
}
