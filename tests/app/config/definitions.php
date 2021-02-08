<?php

use Ep\Helper\Alias;
use Yiisoft\Log\Target\File\FileTarget;

return [
    FileTarget::class => static fn () => new FileTarget(Alias::get(Ep::getConfig()->runtimeDir . '/test.log')),
];
