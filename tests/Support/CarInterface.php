<?php

namespace Ep\Tests\Support;

interface CarInterface
{
    public function getSize(): int;

    public function getEngine(): EngineInterface;

    public function drive(): void;
}
