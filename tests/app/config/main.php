<?php

return [
    'appNamespace' => 'Ep\Tests\App',
    'controllerDirname' => 'Controller',
    'basePath' => dirname(__DIR__),
    'env' => 'test',
    'debug' => true,
    'router' => require('router.php'),
    'params' => require('params.php')
];
