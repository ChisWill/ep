<?php

declare(strict_types=1);

use Ep\Tests\App\Component\Bootstrap;

return [
    'rootNamespace' => 'Ep\Tests\App',
    'vendorPath' => dirname(__DIR__, 3) . '/vendor',
    'env' => 'test',
    'debug' => true,
    'configureHandlers' => [
        Bootstrap::class
    ],
    'secretKey' => env('SECRET_KEY'),
    'di' => require('di.php'),
    'route' => require('route.php'),
    'events' => require('events.php'),
    'params' => import('params')
];
