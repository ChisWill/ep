<?php

declare(strict_types=1);

use Ep\Base\Config;
use Ep\Contract\InterceptorInterface;
use Ep\Contract\WebErrorRendererInterface;
use Ep\Tests\App\Component\ErrorRenderer;
use Ep\Tests\App\Component\Interceptor;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Log\Target\File\FileTarget;

return static fn (Config $config): array => [
    WebErrorRendererInterface::class => ErrorRenderer::class,
    InterceptorInterface::class => Interceptor::class,
    FileTarget::class => static fn (Aliases $aliases): FileTarget => new FileTarget($aliases->get($config->runtimeDir . '/logs/' . date('Y-m-d') . '.log'))
];
