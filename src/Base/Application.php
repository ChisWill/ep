<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use RuntimeException;
use Throwable;

abstract class Application
{
    public function __construct(array $config)
    {
        set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline, array $context) {
            throw new RuntimeException(sprintf('%s, in %s:%d', $errstr, $errfile, $errline));
        }, E_ALL);

        Ep::init($config);
    }

    public function run()
    {
        try {
            return $this->handle();
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage() . ' -> ' . $e->getTraceAsString(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @return int|void
     */
    protected abstract function handle();
}
