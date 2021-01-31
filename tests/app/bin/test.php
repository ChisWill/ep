#!/usr/bin/env php
<?php

require(__DIR__ . '/../../../vendor/autoload.php');

$application = new Ep\Console\Application(require(dirname(__DIR__) . '/config/main.php'));
