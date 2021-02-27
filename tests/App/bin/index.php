#!/usr/bin/env php
<?php

use Ep\Console\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(require(dirname(__DIR__) . '/config/main.php'));

Ep::getDi()->get(Application::class)->run();

exit(0);