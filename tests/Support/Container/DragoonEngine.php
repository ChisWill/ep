<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Container;

class DragoonEngine implements EngineInterface
{
    public int $power;

    public function __construct(int $power = 50)
    {
        $this->power = $power;
    }

    public function getPower(): int
    {
        return $this->power;
    }
}
