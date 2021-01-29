<?php

return [
    'appNamespace' => 'Ep\Tests\App',
    'controllerDirname' => 'Controller',
    'basePath' => dirname(__DIR__),
    'routeRules' => [
        'test/<ctrl:.+>/<act:.+>' => '<ctrl>/<act>',
        'test/<ctrl:.+>/<act:.+>/<id:\d+>' => '<ctrl>/<act>',
    ],
    'params' => require('params.php')
];
