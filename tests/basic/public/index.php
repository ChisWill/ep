<?php

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

$application = new Ep\Web\Application(require(dirname(__DIR__) . '/config/main.php'));

$application->run();
