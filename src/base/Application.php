<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\base\Controller;
use Exception;
use RuntimeException;
use Ep\Helper\Alias;

abstract class Application
{
    protected Config $config;

    public function __construct(array $config)
    {
        $this->init($config);
    }

    private function init(array $config): void
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new Exception(sprintf('%s, in %s:%d', $errstr, $errfile, $errline));
        }, E_ALL);

        Ep::setConfig($config);

        Alias::set('@root', $config['basePath']);
        Alias::set('@ep', dirname(__DIR__, 2));

        Ep::setDi(require(Alias::get('@ep/config/definition.php')), [ServiceProvider::class]);
    }

    public function run(): int
    {
        try {
            return $this->handle();
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    protected function createController(string $controllerName): Controller
    {
        if (!class_exists($controllerName)) {
            throw new RuntimeException("{$controllerName} is not found.");
        }
        return new $controllerName;
    }

    protected function runAction(string $controllerName, string $actionName)
    {
        if (!method_exists($controller, $actionName)) {
            throw new RuntimeException("{$actionName} is not found.");
        }
        return call_user_func([$controller, $actionName], $request);
    }

    protected abstract function handle(): int;
}
