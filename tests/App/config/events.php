<?php

declare(strict_types=1);

use Ep\Event\AfterRequest;
use Ep\Event\BeforeRequest;
use Ep\Tests\App\Component\Event;
use Ep\Tests\App\Controller\DemoController;

return [
    DemoController::class => [
        function (DemoController $event) {
            echo $event->id;
        }
    ],
    BeforeRequest::class => [
        [Event::class, 'before']
    ],
    AfterRequest::class => [
        [Event::class, 'after']
    ]
];
