<?php

use Ep\Tests\App\web\Controller\IndexController;
use FastRoute\RouteCollector;

return function (RouteCollector $route) {
    $route->addGroup('/api', function (RouteCollector $r) {
        $r->get('/index/error', [IndexController::class => 'error']);
    });
    $route->get('/index/do', [IndexController::class => 'do']);
};
