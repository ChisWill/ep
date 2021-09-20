<?php

declare(strict_types=1);

namespace Ep\Tests\App\Objects\Human;

use Ep\Annotation\Inject;
use Ep\Tests\App\Objects\Weapon\Bow;

class GrandPa
{
    /**
     * @Inject
     */
    private Bow $bow;

    private function getWeapon(): string
    {
        return get_class($this->bow);
    }

    public function shoot(): string
    {
        return $this->getWeapon() . '<br>';
    }
}
