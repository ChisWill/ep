<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

use Ep\Tests\Support\Container\XEngine;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use ReflectionClass;

/**
 * @Iterations(5)
 * @Revs(1000)
 * @Groups({"base"})
 * @BeforeMethods({"before"})
 */
class ReflectBench
{
    private $class;

    private const COUNT = 100;

    public function before()
    {
        $this->class = new XEngine();
    }

    public function benchOther()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            // new XEngine();
            $ref = new ReflectionClass(XEngine::class);
        }
    }

    public function benchClass()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            $ref = new ReflectionClass($this->class);
        }
    }

    public function benchProperty()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            $ref = new ReflectionClass($this->class);
            $properties = $ref->getProperties();
            foreach ($properties as $p) {
                $p->getName();
            }
        }
    }

    public function benchProperties()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            $ref = new ReflectionClass($this->class);
            $n = $ref->getProperty('power');
            // $ref->getProperty('price');
            // $ref->getProperty('params');
            // $ref->getProperty('callback');
            // $ref->getProperty('a');
            // $ref->getProperty('b');
            // $ref->getProperty('c');
            // $ref->getProperty('d');
            // $ref->getProperty('e');
            // $ref->getProperty('f');
        }
    }
}
