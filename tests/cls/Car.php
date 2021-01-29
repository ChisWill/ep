<?php

namespace Ep\Tests\Cls;

class Car
{
    public string $color;
    public int $size;

    public function __construct()
    {
    }

    public function setSize(int $size)
    {
        $this->size = $size;
    }

    public function info()
    {
    }
}
