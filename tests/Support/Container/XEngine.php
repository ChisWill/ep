<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Container;

use Closure;

class XEngine implements EngineInterface
{
    public int $power;
    public int $price;
    public array $params;
    public Closure $callback;
    public int $a;
    public int $b;
    public int $c;
    public int $d;
    public int $e;
    public int $f;

    public function __construct(int $power = 30)
    {
        $this->power = $power;
    }

    public function __toString()
    {
        return 'I am ' . static::class;
    }

    public function getPower(): int
    {
        return $this->power;
    }

    public function withPower(int $power = 30)
    {
        $new = clone $this;
        $new->power = $power;
        return $new;
    }

    public function setPrice(int $price)
    {
        $this->price = $price;
        return $this;
    }

    public function withParams(array $params)
    {
        $new = clone $this;
        $new->params = $params;
        return $new;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function withCallback(Closure $callback)
    {
        $new = clone $this;
        $new->callback = $callback;
        return $new;
    }

    public function setCallback(Closure $callback)
    {
        $this->callback = $callback;
        return $this;
    }
}
