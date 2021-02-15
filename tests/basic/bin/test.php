#!/usr/bin/env php
<?php

require(dirname(__DIR__, 3) . '/vendor/autoload.php');

$application = new Ep\Console\Application(require(dirname(__DIR__) . '/config/main.php'));
