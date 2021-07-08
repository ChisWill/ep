<?php

declare(strict_types=1);

use Ep\Tests\App\Advance\TestDir\BackAdmin\Command\ADGTeCommand;
use Ep\Tests\App\Advance\TestDir\BackAdmin\Controller\AdTestController;
use Ep\Tests\App\Controller\StateController;
use FastRoute\RouteCollector;

return function (RouteCollector $route): void {
    $route->get('/site', 'index/index');
    $route->get('/ping', [StateController::class, 'ping']);
    $route->get('/advance/say', [AdTestController::class, 'say']);
    $route->get('/advance/run', [AdTestController::class, 'run']);
    $route->get('/advance/test', [ADGTeCommand::class, 'say']);
};
