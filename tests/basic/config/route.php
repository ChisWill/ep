<?php

use Ep\Tests\Basic\Controller\StateController;
use FastRoute\RouteCollector;

return function (RouteCollector $route) {
    $route->get('/site', 'index/index');
    $route->get('/ping', [StateController::class, 'ping']);
};
