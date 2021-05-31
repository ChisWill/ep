<?php

declare(strict_types=1);

use Ep\Command\TestCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

require(__DIR__ . '/vendor/autoload.php');

$application = new Application();

$input = new ArgvInput();

$command = new TestCommand();
$command->setName('test');
$application->add($command);

$n = $application->run();

tt($n);
