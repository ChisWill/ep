<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Weapon;

final class Gun implements WeaponInterface
{
    public function getDamage(): int
    {
        return 5000;
    }
}
