<?php

declare(strict_types=1);

use Ep\Tests\App\Advance\TestDir\BackAdmin\Command\ADGTeCommand;
use Ep\Tests\App\Advance\TestDir\BackAdmin\Controller\AdTestController;
use Ep\Tests\App\Controller\StateController;
use FastRoute\RouteCollector;
use Yiisoft\Http\Method;

return function (RouteCollector $route): void {
    $route->get('/site', 'index/index');
    $route->addGroup('/try', function (RouteCollector $route) {
        $route->addRoute(Method::ALL, '/{action:[a-zA-Z][\w-]*}', 'test/<action>');
    });
    $route->get('/ping', [StateController::class, 'ping']);
    $route->get('/advance/say', [AdTestController::class, 'say']);
    $route->get('/advance/run', [AdTestController::class, 'run']);
    $route->get('/advance/test', [ADGTeCommand::class, 'say']);
};
