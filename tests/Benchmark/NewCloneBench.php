<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Yiisoft\Yii\Web\SapiEmitter;

/**
 * @Iterations(5)
 * @Revs(1000)
 * @Groups({"base"})
 * @BeforeMethods({"before"})
 */
class NewCloneBench
{
    private $class;
    private const COUNT = 1000;

    public function before()
    {
        $this->class = new SapiEmitter();
    }

    public function benchNew()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            new SapiEmitter();
        }
    }

    public function benchClone()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            clone $this->class;
        }
    }

    public function benchArrow()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            fn () => new SapiEmitter();
        }
    }

    public function benchClosure()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            function () use ($i) {
                new SapiEmitter();
            };
        }
    }
}
