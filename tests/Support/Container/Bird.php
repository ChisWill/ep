<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Container;

class Bird
{
    public int $speed = 10;

    public WingInterface $wing;

    public function __construct(WingInterface $wing)
    {
        $this->wing = $wing;
    }

    public function fly(): void
    {
        echo 'Now Speed:' . $this->wing->getSpeed();
        echo '<br>';
        $this->wing->addSpeed(20);
        echo 'After Speed:' . $this->wing->getSpeed();
    }
}
