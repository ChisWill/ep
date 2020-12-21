<?php

require(__DIR__ . '/../../../vendor/autoload.php');

define('APP_PATH', dirname(__DIR__));

$core = new ep\Core;
$config = new ep\web\Config;
$config->controllerNamespace = 'webapp\\controller';
$config->viewFilePath = APP_PATH . '/view';
$config->routeRules = require(__DIR__ . '/../config/router.php');

$core->run($config);
