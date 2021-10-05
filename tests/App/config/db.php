<?php

declare(strict_types=1);

return [
    'redis' => [
        'hostname' => 'localhost',
        'database' => 0,
        'password' => null,
        'port' => 6379,
    ],
    'mysql' => [
        'dsn' => env('MYSQL_DSN'),
        'username' => env('MYSQL_USERNAME'),
        'password' => env('MYSQL_PASSWORD'),
    ]
];
