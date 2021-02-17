<?php

require(__DIR__ . '/../../../vendor/autoload.php');

$application = new Ep\Web\Application(require(dirname(__DIR__) . '/config/main.php'));

$application->run();
