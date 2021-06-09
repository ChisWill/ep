<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

use Ep\Tests\Support\Container\XEngine;
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
class SetWithBench
{
    private XEngine $setEngine;
    private XEngine $withEngine;

    private const COUNT = 1000;

    public function before()
    {
        $this->setEngine = new XEngine(100);
        $this->withEngine = new XEngine(50);
    }

    public function benchSet()
    {
        $result = [];
        for ($i = 0; $i < self::COUNT; $i++) {
            $r = $this->setEngine
                ->setPrice(10)
                ->setParams([
                    'foo' => 'bar',
                    'bar' => 'foo'
                ])
                ->setCallback(fn ($n): int => $n * $n);
            $result[] = $r;
        }
    }

    public function benchWith()
    {
        $result = [];
        for ($i = 0; $i < self::COUNT; $i++) {
            $r = $this->withEngine
                ->withPower(55)
                ->withParams([
                    'foo' => 'bar',
                    'bar' => 'foo'
                ])
                ->withCallback(fn ($n): int => $n * $n);
            $result[] = $r;
        }
    }
}
