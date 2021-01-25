<?php

use Tests\App\config\WebConfig;
use ep\Core;

require(__DIR__ . '/../../../vendor/autoload.php');

$core = new Core(dirname(__DIR__));

$core->run(new WebConfig);
