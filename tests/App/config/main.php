<?php

declare(strict_types=1);

return [
    'rootNamespace' => 'Ep\Tests\App',
    'rootPath' => dirname(__DIR__, 1),
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'env' => 'test',
    'debug' => true,
    'secretKey' => '8FFA893E119A32D0C6A686863217A181',
    'di' => require('di.php'),
    'route' => require('route.php'),
    'events' => require('events.php'),
    'params' => import('params')
];
