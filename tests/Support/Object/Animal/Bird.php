<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Animal;

use Ep\Tests\Support\Object\Wing\WingInterface;

class Bird extends Animal
{
    public function __construct(WingInterface $wing)
    {
        $this->wing = $wing;
    }

    public function getName(): string
    {
        return 'Tidy bird';
    }

    public function getSize(): int
    {
        return 1;
    }

    public function getPower(): int
    {
        return 0;
    }

    public function getSpeed(): int
    {
        return $this->wing->getSpeed();
    }

    public function isFly(): bool
    {
        return true;
    }

    public function isWalk(): bool
    {
        return false;
    }
}
