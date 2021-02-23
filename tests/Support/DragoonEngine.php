<?php

namespace Ep\Tests\Support;

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
