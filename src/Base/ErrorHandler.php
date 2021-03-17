<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ConfigurableInterface;
use Ep\Contract\ConfigurableTrait;
use Ep\Contract\ErrorRendererInterface;
use ErrorException;
use Throwable;

final class ErrorHandler implements ConfigurableInterface
{
    use ConfigurableTrait;

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

    private ErrorRendererInterface $errorRenderer;

    public function __construct(ErrorRendererInterface $errorRenderer)
    {
        $this->errorRenderer = $errorRenderer;
    }

    /**
     * @param mixed $request
     */
    public function register($request): void
    {
        set_exception_handler(fn (Throwable $e) => $this->handleException($e, $request));
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError'], $request);
    }

    /**
     * @param mixed $request
     */
    public function handleException(Throwable $t, $request): void
    {
        $this->unregister();

        $this->errorRenderer->log($t, $request);

        echo $this->errorRenderer->render($t, $request);
        exit(1);
    }

    public function handleError(int $severity, string $message, string $file, int $line): void
    {
        if (!(error_reporting() & $severity)) {
            return;
        }

        throw new ErrorException($message, $severity, $severity, $file, $line);
    }

    /**
     * @param mixed $request
     */
    public function handleFatalError($request): void
    {
        $error = error_get_last();
        if ($error !== null && $this->isFatalError($error)) {
            $exception = new ErrorException(
                $error['message'],
                $error['type'],
                $error['type'],
                $error['file'],
                $error['line']
            );
            $this->handleException($exception, $request);
        }
    }

    public static function convertToString(Throwable $t): string
    {
        return "Exception '" . get_class($t) . "' with message '{$t->getMessage()}' \n\nin "
            . $t->getFile() . ':' . $t->getLine() . "\n\n"
            . "Stack trace:\n" . $t->getTraceAsString();
    }

    public static function getErrorName(int $severity): string
    {
        return self::ERRORS[$severity] ?? 'Error';
    }

    private function isFatalError(array $error): bool
    {
        return in_array($error['type'] ?? null, [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING], true);
    }

    private function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }
}
