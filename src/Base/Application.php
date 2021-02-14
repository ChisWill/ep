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
        $request = $this->createRequest();

        $this->register($request);

        $this->handleRequest($request);
    }

    abstract protected function createRequest();

    abstract protected function register($request): void;

    abstract protected function handleRequest($request): void;
}
