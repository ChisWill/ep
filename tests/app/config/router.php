<?php

use Ep\Tests\App\web\Controller\IndexController;
use FastRoute\RouteCollector;

return function (RouteCollector $route) {
    $route->addGroup('/api', function (RouteCollector $r) {
        $r->get('/user/list', 'api/user/list');
    });
    $route->addGroup('/shop{sid:\d+}', function (RouteCollector $r) {
        $r->get('/{ctrl:\w+}/{act:\w+}', 'shop/<ctrl>/<act>');
    });
};
