<?php

namespace Ep\Tests\Classes;

class XEngine implements EngineInterface
{
    public int $power;

    public function __construct(int $power = 30)
    {
        $this->power = $power;
    }

    public function getPower(): int
    {
        return $this->power;
    }
}
