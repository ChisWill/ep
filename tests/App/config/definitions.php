<?php

declare(strict_types=1);

use Ep\Contract\InterceptorInterface;
use Ep\Contract\WebErrorHandlerInterface;
use Ep\Tests\App\Component\Interceptor;
use Ep\Tests\App\Handler\ErrorHandler;

return [
    WebErrorHandlerInterface::class => ErrorHandler::class,
    // InterceptorInterface::class => Interceptor::class
];
