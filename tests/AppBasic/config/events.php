<?php

use Ep\Tests\Basic\Controller\DemoController;
use Ep\Tests\Support\XEngine;

return [
    XEngine::class => [
        function (XEngine $event) {
            echo $event->getPower();
        }
    ]
];
