<?php

declare(strict_types=1);

use Ep\Tests\App\Advance\FrontEnd\Controller\TestEPRunController;
use Ep\Tests\App\Controller\StateController;
use FastRoute\RouteCollector;
use Yiisoft\Http\Method;

return function (RouteCollector $route): void {
    $route->get('/site', 'state/ping');
    $route->addGroup('/try', function (RouteCollector $route) {
        $route->addRoute(Method::ALL, '/{action:[a-zA-Z][\w-]*}', 'test/<action>');
    });
    $route->get('/ping', [StateController::class, 'ping']);
    $route->get('/advance/say', [TestEPRunController::class, 'say']);
    $route->get('/advance/run', [TestEPRunController::class, 'run']);
};
