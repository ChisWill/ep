<?php

declare(strict_types=1);

namespace Ep\Base;

use Yiisoft\Di\Container;
use Yiisoft\Di\Support\DeferredServiceProvider;

class ServiceProvider extends DeferredServiceProvider
{
    public function provides(): array
    {
        return [];
    }

    public function register(Container $container): void
    {
        $this->registerDependencies($container);
        $this->registerService($container);
    }

    protected function registerDependencies(Container $container): void
    {
    }

    protected function registerService(Container $container): void
    {
    }
}
