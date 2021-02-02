<?php

return [
    'appNamespace' => 'Ep\Tests\App',
    'basePath' => dirname(__DIR__) . '/src',
    'env' => 'test',
    'debug' => true,
    'secretKey' => '8FFA893E119A32D0C6A686863217A181',
    'route' => require('route.php'),
    'params' => require('params.php')
];
