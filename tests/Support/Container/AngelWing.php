<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Container;

use Closure;

class AngelWing implements WingInterface
{
    private int $speed;

    public function __construct(int $speed = 30)
    {
        $this->speed = $speed;
    }

    public function addSpeed(int $speed): void
    {
        $this->speed += $speed;
    }

    public function getSpeed(): int
    {
        return $this->speed;
    }
}
