<?php

use Ep\Tests\App\Web\Controller\DemoController;

return [
    DemoController::class => [fn (DemoController $event) => tes($event->testAction())]
];
