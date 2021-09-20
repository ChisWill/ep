<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Wing;

final class AngelWing implements WingInterface
{
    private int $speed = 10;

    public function addSpeed(int $speed): void
    {
        $this->speed += $speed;
    }

    public function getSpeed(): int
    {
        return $this->speed;
    }

    public function fly(): void
    {
        echo sprintf('fly:%d<br>', $this->speed);
    }
}
