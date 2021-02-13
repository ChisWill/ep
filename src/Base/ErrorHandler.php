<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Exception\ErrorException;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class ErrorHandler
{
    public bool $detail;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->init();
    }

    public function register(): void
    {
        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError']);
    }

    public function handleException(Throwable $e): void
    {
        $this->unregister();

        $this->log($e);

        echo $this->renderException($e);
        exit(1);
    }

    public function handleError(int $code, string $message, string $file, int $line): void
    {
        if (!(error_reporting() & $code)) {
            return;
        }

        throw new ErrorException($message, $code, $code, $file, $line);
    }

    public function handleFatalError(): void
    {
        $error = error_get_last();
        if ($error !== null && ErrorException::isFatalError($error)) {
            $exception = new ErrorException(
                $error['message'],
                $error['type'],
                $error['type'],
                $error['file'],
                $error['line']
            );
            $this->handleException($exception);
        }
    }

    protected function convertToString(Throwable $e): string
    {
        return "Exception '" . get_class($e) . "' with message '{$e->getMessage()}' \n\nin "
            . $e->getFile() . ':' . $e->getLine() . "\n\n"
            . "Stack trace:\n" . $e->getTraceAsString();
    }

    private function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    private function log(Throwable $e): void
    {
        $this->logger->error($this->convertToString($e));
    }

    protected abstract function init(): void;

    abstract protected function renderException(Throwable $e): string;
}
