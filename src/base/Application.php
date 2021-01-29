<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
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

    protected abstract function handle(): int;
}
