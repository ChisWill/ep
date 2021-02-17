#!/usr/bin/env php
<?php

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

$application = new Ep\Swoole\Server(require(dirname(__DIR__) . '/config/swoole.php'));

$application->run();
