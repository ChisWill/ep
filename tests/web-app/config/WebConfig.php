<?php

namespace tests\webapp\config;

use ep\web\Config;

class WebConfig extends Config
{
    public string $controllerNamespace = 'tests\\webapp\\controller';

    public array $mysql = [
        'dsn' => 'mysql:host=127.0.0.1;dbname=test',
        'username' => 'root',
        'password' => ''
    ];

    public function __construct()
    {
        parent::__construct();

        $this->setDi([]);
    }
}
