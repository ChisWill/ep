#!/usr/bin/env php
<?php

declare(strict_types=1);

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

Ep::init(require(dirname(__DIR__) . '/config/main.php'));
