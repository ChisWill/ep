<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Console\Command;
use Ep\Contract\ErrorRendererInterface;
use ErrorException;
use Throwable;

final class ErrorHandler
{
    private ErrorRendererInterface $errorRenderer;

    /**
     * @param mixed $request
     */
    public function register($request, ErrorRendererInterface $errorRenderer): void
    {
        $new = clone $this;
        $new->errorRenderer = $errorRenderer;

        set_exception_handler(fn (Throwable $e) => $new->handleException($e, $request));
        set_error_handler([$new, 'handleError']);
        register_shutdown_function([$new, 'handleFatalError'], $request);
    }

    /**
     * @param mixed $request
     */
    public function handleException(Throwable $t, $request): void
    {
        $this->unregister();

        echo $this->errorRenderer->render($t, $request);

        exit(Command::FAIL);
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
