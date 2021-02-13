<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;

abstract class Application
{
    public function __construct(array $config)
    {
        Ep::init($config);
    }

    public function run(): void
    {
        $this->register();

        $this->handle();
    }

    protected abstract function handle(): void;

    protected abstract function register(): void;
}
