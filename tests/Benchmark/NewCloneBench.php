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

    public function before()
    {
        $this->class = new SapiEmitter();
    }

    public function benchNew()
    {
        new SapiEmitter();
    }

    public function benchClone()
    {
        clone $this->class;
    }
}
