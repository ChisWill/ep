<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Weapon;

interface WeaponInterface
{
    public function getSpeed(): int;

    public function fly(): void;
}
