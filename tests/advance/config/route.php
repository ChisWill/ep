<?php

use FastRoute\RouteCollector;

return function (RouteCollector $route) {
    $route->get('/', 'web/index/index');
    $route->addGroup('/ad/api', function (RouteCollector $r) {
        $r->get('/v{ver:\d+}/{ctrl:[a-zA-Z]\w*}/{act:[a-zA-Z]\w*}', 'api//v<ver>/<ctrl>/<act>');
    });
};
