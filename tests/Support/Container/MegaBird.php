<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Container;

class MegaBird
{
    public int $speed = 10;

    public WingInterface $wing;
    public EngineInterface $engine;

    public function __construct(WingInterface $wing, EngineInterface $engine)
    {
        $this->wing = $wing;
        $this->engine = $engine;
    }

    public function addSpeed(int $speed): void
    {
        $this->speed += $speed;
    }

    public function getWing(): WingInterface
    {
        return $this->wing;
    }

    public function getEngine(): EngineInterface
    {
        return $this->engine;
    }

    public function fly(): void
    {
        echo 'Now Speed:' . $this->wing->getSpeed() * $this->engine->getPower();
        echo '<br>';
        $this->wing->addSpeed(20);
        echo 'After Speed:' . $this->wing->getSpeed() * $this->engine->getPower();
    }
}
