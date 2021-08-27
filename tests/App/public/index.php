<?php

declare(strict_types=1);

use Ep\Web\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(
    file_exists(dirname(__DIR__) . '/config/main-local.php') ?
        array_merge(
            require(dirname(__DIR__) . '/config/main.php'),
            require(dirname(__DIR__) . '/config/main-local.php')
        ) :
        require(dirname(__DIR__) . '/config/main.php'),
);

$start = microtime(true);

Ep::getDi()->get(Application::class)->run();

$end = microtime(true);

Ep::getLogger()->info(($end - $start) * 1000 . 'ms');
