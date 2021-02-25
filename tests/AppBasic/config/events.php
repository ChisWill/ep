<?php

use Ep\Tests\Basic\Controller\DemoController;

return [
    DemoController::class => [
        function (DemoController $event) {
            echo $event->id;
        }
    ]
];
