<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Animal;

use Ep\Tests\Support\Object\Engine\EngineInterface;
use Ep\Tests\Support\Object\Weapon\WeaponInterface;
use Ep\Tests\Support\Object\Wing\WingInterface;

final class WarBird extends Bird
{
    private WingInterface $wing;
    private EngineInterface $engine;
    private WeaponInterface $weapon;

    public function __construct(
        WingInterface $wing,
        EngineInterface $engine,
        WeaponInterface $weapon
    ) {
        $this->wing = $wing;
        $this->engine = $engine;
        $this->weapon = $weapon;
    }

    public function getName(): string
    {
        return 'Fly Dragon';
    }

    public function getSize(): int
    {
        return 200;
    }

    public function getSpeed(): int
    {
        return $this->wing->getSpeed();
    }

    public function getPower(): int
    {
        return $this->engine->getPower();
    }

    public function getDamage(): int
    {
        return $this->weapon->getDamage();
    }
}
