<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Helper\Alias;
use Throwable;
use RuntimeException;

abstract class Application
{
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

    public function run(): void
    {
        try {
            $this->handle();
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage() . ' -> ' . $e->getTraceAsString(), $e->getCode(), $e->getPrevious());
        }
    }

    protected abstract function handle(): void;
}
