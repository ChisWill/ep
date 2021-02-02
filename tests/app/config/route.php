<?php

use FastRoute\RouteCollector;

return function (RouteCollector $route) {
    $route->addGroup('/api', function (RouteCollector $r) {
        $r->get('/user/list', 'api/user/list');
        $r->get('/user/{id:\d+}', 'api/user/detail');
    });
    $route->addGroup('/shop{sid:\d+}', function (RouteCollector $r) {
        $r->get('/{ctrl:\w+}/{act:\w+}', 'shop/<ctrl>/<act>');
    });
};
