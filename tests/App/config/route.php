<?php

declare(strict_types=1);

use Ep\Tests\App\Controller\StateController;
use FastRoute\RouteCollector;

return function (RouteCollector $route) {
    $route->get('/site', 'index/index');
    $route->get('/ping', [StateController::class, 'ping']);
};
