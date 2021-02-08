<?php

return [
    'appNamespace' => 'Ep\Tests\App',
    'basePath' => dirname(__DIR__),
    'mysqlDsn' => 'mysql:host=127.0.0.1;dbname=test',
    'mysqlUsername' => 'root',
    'mysqlPassword' => '',
    'env' => 'test',
    'debug' => true,
    'secretKey' => '8FFA893E119A32D0C6A686863217A181',
    'errorHandler' => 'web/demo/error',
    'route' => require('route.php'),
    'params' => require('params.php'),
    'definitions' => require('definitions.php')
];
