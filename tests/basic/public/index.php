<?php
require(__DIR__ . '/../../../vendor/autoload.php');

$start = microtime(true);

$application = new Ep\Web\Application(require(dirname(__DIR__) . '/config/main.php'));

$application->run();

$end = microtime(true);

echo '<br>' . ($end - $start) * 1000 . '(ms)';
