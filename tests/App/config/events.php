<?php

declare(strict_types=1);

use Ep\Tests\App\Controller\DemoController;

return [
    DemoController::class => [
        function (DemoController $event) {
            echo $event->id;
        }
    ]
];
