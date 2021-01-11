#!/usr/bin/env php
<?php

use ep\Core;
use tests\webapp\config\ConsoleConfig;

require(__DIR__ . '/../../../vendor/autoload.php');

$core = new Core(__DIR__);

$core->run(new ConsoleConfig);
