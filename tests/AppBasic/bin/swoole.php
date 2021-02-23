#!/usr/bin/env php
<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

$application = new Ep\Swoole\Application(require(dirname(__DIR__) . '/config/swoole.php'));

$application->run();
