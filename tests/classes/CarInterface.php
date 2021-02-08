<?php

namespace Ep\Tests\Classes;

interface CarInterface
{
    public function getSize(): int;

    public function getEngine(): EngineInterface;
}
