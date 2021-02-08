<?php

namespace Ep\Tests\Classes;

class BMW implements CarInterface
{
    public int $size;
    public EngineInterface $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getEngine(): EngineInterface
    {
        return $this->engine;
    }
}
