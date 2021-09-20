<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Engine;

final class SteamEngine implements EngineInterface
{
    public function getPower(): int
    {
        return 10;
    }
}
