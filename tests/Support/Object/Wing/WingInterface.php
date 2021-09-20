<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Wing;

interface WingInterface
{
    public function addSpeed(int $speed): void;

    public function getSpeed(): int;

    public function fly(): void;
}
