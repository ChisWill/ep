<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ErrorRendererInterface;
use Throwable;

abstract class ErrorRenderer implements ErrorRendererInterface
{
    private const ERRORS = [
        E_ERROR => 'PHP Fatal Error',
        E_WARNING => 'PHP Warning',
        E_PARSE => 'PHP Parse Error',
        E_NOTICE => 'PHP Notice',
        E_CORE_ERROR => 'PHP Core Error',
        E_CORE_WARNING => 'PHP Core Warning',
        E_COMPILE_ERROR => 'PHP Compile Error',
        E_COMPILE_WARNING => 'PHP Compile Warning',
        E_USER_ERROR => 'PHP User Error',
        E_USER_WARNING => 'PHP User Warning',
        E_USER_NOTICE => 'PHP User Notice',
        E_STRICT => 'PHP Strict Warning',
        E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
        E_DEPRECATED => 'PHP Deprecated Warning',
        E_USER_DEPRECATED => 'PHP User Deprecated Warning',
    ];

    public function getErrorName(int $severity): string
    {
        return self::ERRORS[$severity] ?? 'Error';
    }

    /**
     * @param mixed $request
     */
    public function render(Throwable $t, $request): string
    {
        return "Exception '" . get_class($t) . "' with message '{$t->getMessage()}' \n\nin "
            . $t->getFile() . ':' . $t->getLine() . "\n\n"
            . "Stack trace:\n" . $t->getTraceAsString() . "\n";
    }

    /**
     * @param mixed $request
     */
    abstract public function log(Throwable $t, $request): void;
}
