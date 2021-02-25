<?php

return [
    'appNamespace' => 'Ep\Tests\Basic',
    'rootPath' => dirname(__DIR__, 1),
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'baseUrl' => '/',
    'mysqlDsn' => 'mysql:host=127.0.0.1;dbname=test',
    'mysqlUsername' => 'root',
    'mysqlPassword' => '',
    'env' => 'test',
    'debug' => true,
    'secretKey' => '8FFA893E119A32D0C6A686863217A181',
    'errorHandler' => 'index/error',
    'route' => require('route.php'),
    'events' => require('events.php'),
    'params' => require('params.php'),
    'definitions' => require('definitions.php')
];