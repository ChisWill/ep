<?php

declare(strict_types=1);

return [
    'appNamespace' => 'Ep\Tests\App',
    'rootPath' => dirname(__DIR__, 1),
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'mysqlDsn' => 'mysql:host=127.0.0.1;dbname=test',
    'mysqlUsername' => 'root',
    'mysqlPassword' => '',
    'env' => 'test',
    'debug' => true,
    'secretKey' => '8FFA893E119A32D0C6A686863217A181',
    'route' => require('route.php'),
    'events' => require('events.php'),
    'params' => require('params.php')
];
