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
        'dsn' => 'mysql:host=127.0.0.1;dbname=test',
        'username' => 'root',
        'password' => '',
    ]
];
