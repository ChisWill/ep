<?php

declare(strict_types=1);

use Ep\Web\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(require(dirname(__DIR__) . '/config/main.php'));

$s = microtime(true);

Ep::getDi()->get(Application::class)->run();

$n = microtime(true);

echo '<br>' . ($n - $s) * 1000;
