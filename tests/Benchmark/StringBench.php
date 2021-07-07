<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

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
class StringBench
{
    private const COUNT = 100;

    private string $rootNamespace;
    private string $controllerNamespace;
    private string $suffix;

    public function before()
    {
        $this->rootNamespace = 'Ep\Tests\App';
        $this->controllerNamespace = 'Ep\Tests\App\Shop\Admin\Controller\v1\user\DemoController';
        $this->suffix = 'Controller';
    }

    public function benchStrReplace()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            str_replace($this->rootNamespace . '\\', '', substr($this->controllerNamespace, 0, strpos($this->controllerNamespace, $this->suffix) - 1));
        }
    }

    public function benchSubstr()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            substr(substr($this->controllerNamespace, 0, strpos($this->controllerNamespace, $this->suffix) - 1), strlen($this->rootNamespace) + 1);
        }
    }

    public function benchStrpos()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            if (strpos($this->controllerNamespace, '/' . ltrim('Controller\v1\user', '/')) === 0) {
            }
        }
    }

    public function benchPregMatch()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            if (preg_match('~Controller.*~', $this->controllerNamespace)) {
            }
        }
    }
}
