<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

use Ep;
use Ep\Base\Config;
use Ep\Kit\Annotate;
use Ep\Tests\App\Controller\DemoController;
use Ep\Tests\App\Controller\TestController;
use Ep\Tests\Support\Container\DragoonEngine;
use Ep\Tests\Support\Container\XEngine;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;

/**
 * @Iterations(5)
 * @Revs(1000)
 * @Groups({"base"})
 * @BeforeMethods({"before"})
 */
class AnnoateBench
{
    private $annoClass;
    private $normalClass;
    private Annotate $annotate;
    private Config $config;

    private const COUNT = 10;

    public function before()
    {
        Ep::init(require(dirname(__DIR__, 1) . '/App/config/main.php'));

        $this->annoClass = Ep::getDi()->get(TestController::class);
        $this->normalClass = Ep::getDi()->get(DemoController::class);
        $this->annotate = Ep::getDi()->get(Annotate::class);
        $this->config = Ep::getConfig();
    }

    public function benchDebugAnnoClass()
    {
        $this->config->debug = true;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->class($this->annoClass);
        }
    }

    public function benchDebugNormalClass()
    {
        $this->config->debug = true;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->class($this->normalClass);
        }
    }

    public function benchAnnoClass()
    {
        $this->config->debug = false;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->class($this->annoClass);
        }
    }

    public function benchNormalClass()
    {
        $this->config->debug = false;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->class($this->normalClass);
        }
    }

    public function benchDebugAnnoProperty()
    {
        $this->config->debug = true;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->property($this->annoClass);
        }
    }

    public function benchDebugNormalProperty()
    {
        $this->config->debug = true;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->property($this->normalClass);
        }
    }

    public function benchAnnoProperty()
    {
        $this->config->debug = false;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->property($this->annoClass);
        }
    }

    public function benchNormalProperty()
    {
        $this->config->debug = false;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->property($this->normalClass);
        }
    }

    public function benchDebugAnnoMethod()
    {
        $this->config->debug = true;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->method($this->annoClass, 'aspectAction');
        }
    }

    public function benchDebugNormalMethod()
    {
        $this->config->debug = true;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->method($this->normalClass, 'testAction');
        }
    }

    public function benchAnnoMethod()
    {
        $this->config->debug = false;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->method($this->annoClass, 'aspectAction');
        }
    }

    public function benchNormalMethod()
    {
        $this->config->debug = false;
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->annotate->method($this->normalClass, 'testAction');
        }
    }
}
