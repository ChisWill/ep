<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Animal;

interface AnimalInterface
{
    public function getName(): string;

    public function getSize(): int;

    public function getPower(): int;

    public function getSpeed(): int;

    public function isFly(): bool;

    public function isWalk(): bool;
}
