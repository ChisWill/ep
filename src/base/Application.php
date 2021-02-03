<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Throwable;
use RuntimeException;
use Ep\Helper\Alias;
use Ep\Standard\ControllerInterface;

abstract class Application
{
    protected int $exitStatus = 0;

    public function __construct(array $config)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new RuntimeException(sprintf('%s, in %s:%d', $errstr, $errfile, $errline));
        }, E_ALL);

        Ep::init($config);

        Alias::set('@root', $config['basePath']);
        Alias::set('@ep', dirname(__DIR__, 2));

        Ep::setDi(require(Alias::get('@ep/config/definition.php')), [ServiceProvider::class]);
    }

    public function run(): int
    {
        try {
            $this->handle();
            return $this->exitStatus;
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage() . ' -> ' . $e->getTraceAsString(), $e->getCode(), $e->getPrevious());
        }
    }

    protected function createController(string $controllerName): ControllerInterface
    {
        if (!class_exists($controllerName)) {
            throw new RuntimeException("{$controllerName} is not found.");
        }
        return Ep::getDi()->get($controllerName);
    }

    protected abstract function handle(): void;
}
