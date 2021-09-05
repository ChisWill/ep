#!/usr/bin/env php
<?php

declare(strict_types=1);

use Ep\Console\Application;

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(import('main'))->get(Application::class)->run();
