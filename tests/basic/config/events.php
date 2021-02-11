<?php

use Ep\Tests\Basic\Controller\DemoController;

return [
    DemoController::class => [fn (DemoController $event) => tes($event->testAction())]
];
