<?php

declare(strict_types=1);

namespace Ep\Tests\App\Objects\Human;

use Ep\Annotation\Inject;
use Ep\Tests\App\Objects\Weapon\Sword;

class Father extends GrandPa
{
    /**
     * @Inject
     */
    private Sword $sword;

    private function getWeapon(): string
    {
        return get_class($this->sword);
    }

    public function fight(): string
    {
        return $this->shoot() . $this->getWeapon() . '<br>';
    }
}
