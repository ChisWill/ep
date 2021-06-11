<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

use Closure;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @Iterations(5)
 * @Revs(1000)
 * @Groups({"base"})
 * @BeforeMethods({"before"})
 */
class ReflectBench
{
    private Closure $closure;

    private const COUNT = 1000;

    public function before()
    {
        $this->closure = fn ($i) => $i;
    }

    public function benchVar()
    {
        $c = $this->closure;
        for ($i = 0; $i < self::COUNT; $i++) {
            $c($i);
        }
    }

    public function benchInvoke()
    {
        $c = $this->closure;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->closure->__invoke($i);
        }
    }

    public function benchCall()
    {
        $c = $this->closure;
        for ($i = 0; $i < self::COUNT; $i++) {
            call_user_func($this->closure, $i);
        }
    }

    public function benchCallArray()
    {
        $c = $this->closure;
        for ($i = 0; $i < self::COUNT; $i++) {
            call_user_func_array($this->closure, [$i]);
        }
    }
}
