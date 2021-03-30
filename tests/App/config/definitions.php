<?php

declare(strict_types=1);

use Ep\Contract\InterceptorInterface;
use Ep\Contract\WebErrorRendererInterface;
use Ep\Tests\App\Component\ErrorRenderer;
use Ep\Tests\App\Component\Interceptor;

return [
    WebErrorRendererInterface::class => ErrorRenderer::class,
    InterceptorInterface::class => Interceptor::class
];
