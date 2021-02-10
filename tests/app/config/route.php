<?php

use Ep\Tests\App\Controller\SiteController;
use FastRoute\RouteCollector;

return function (RouteCollector $route) {
    $route->get('/site/info', [SiteController::class, 'userInfo']);
    $route->addGroup('/api', function (RouteCollector $r) {
        $r->get('/v{ver:\d+}/{ctrl:[a-zA-Z]\w*}/{act:[a-zA-Z]\w*}', 'api//v<ver>/<ctrl>/<act>');
    });
};
