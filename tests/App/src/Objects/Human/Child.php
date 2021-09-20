<?php

declare(strict_types=1);

namespace Ep\Tests\App\Objects\Human;

use Ep\Annotation\Inject;
use Ep\Tests\App\Objects\Weapon\Gun;

final class Child extends Father
{
    /**
     * @Inject
     */
    private Gun $gun;

    private function getWeapon(): string
    {
        return get_class($this->gun);
    }

    public function do(): string
    {
        return $this->fight() . $this->getWeapon() . '<br>';
    }
}
