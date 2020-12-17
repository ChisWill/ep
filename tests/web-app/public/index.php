<?php

require(__DIR__ . '/../../../vendor/autoload.php');

$core = new ep\Core;
$config = new ep\web\Config;
$config->controllerNamespace = 'webapp\\controller';
$config->routeRules = require(__DIR__ . '/../config/router.php');

$core->run($config);
