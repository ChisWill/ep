<?php

use Ep\Tests\App\Controller\DemoController;

return [
    DemoController::class => [
        function (DemoController $event) {
            echo $event->id;
        }
    ]
];
