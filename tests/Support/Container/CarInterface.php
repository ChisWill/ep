<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Container;

interface CarInterface
{
    public function getSize(): int;

    public function getEngine(): EngineInterface;

    public function drive(): void;
}
