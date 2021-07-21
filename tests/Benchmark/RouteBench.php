<?php

declare(strict_types=1);

namespace Ep\Tests\Benchmark;

use Ep;
use Ep\Base\Route;
use Ep\Contract\InjectorInterface;
use Ep\Tests\App\Controller\StateController;
use Ep\Web\ControllerRunner;
use HttpSoft\Message\ServerRequest;
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
class RouteBench
{
    private const COUNT = 1;

    private string $stringHandler = 'state/ping';
    private array $arrayHandler = [StateController::class, 'ping'];
    private InjectorInterface $injector;
    private ControllerRunner $runner;
    private Route $route;

    public function before()
    {
        Ep::init(require(dirname(__DIR__, 1) . '/App/config/main.php'));

        $this->injector = Ep::getInjector();
        $this->route = Ep::getDi()->get(Route::class)->withRule(Ep::getConfig()->getRouteRule());
        $this->runner = $this->injector->make(ControllerRunner::class);
    }

    public function benchRoute()
    {
        $path = '/site';
        for ($i = 0; $i < self::COUNT; $i++) {
            [, $result] = $this->route->match($path);
        }
    }

    public function benchRunStr()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->runner->run($this->stringHandler, new ServerRequest());
        }
    }

    public function benchRunArr()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            $this->runner->run($this->arrayHandler, new ServerRequest());
        }
    }

    public function benchString()
    {
        $path = '/site';
        for ($i = 0; $i < self::COUNT; $i++) {
            [, $result] = $this->route->match($path);
            $this->runner->run($result, new ServerRequest());
        }
    }

    public function benchArray()
    {
        $path = '/ping';
        for ($i = 0; $i < self::COUNT; $i++) {
            [, $result] = $this->route->match($path);
            $this->runner->run($result, new ServerRequest());
        }
    }
}
