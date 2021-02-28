<?php

declare(strict_types=1);

use Ep\Contract\ErrorHandlerInterface;
use Ep\Tests\App\Handler\ErrorHandler;

return [
    ErrorHandlerInterface::class => ErrorHandler::class
];
