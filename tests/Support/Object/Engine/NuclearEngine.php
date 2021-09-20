<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Engine;

final class NuclearEngine implements EngineInterface
{
    private int $power;

    public function __construct(int $power = 100)
    {
        $this->power = $power;
    }

    public function getPower(): int
    {
        return $this->power * 10000;
    }
}
