<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Container;

interface WingInterface
{
    public function addSpeed(int $speed): void;

    public function getSpeed(): int;
}
