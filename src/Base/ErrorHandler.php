<?php

declare(strict_types=1);

namespace Ep\Base;

use Yiisoft\Yii\Web\SapiEmitter;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use ErrorException;
use Throwable;

abstract class ErrorHandler
{
    private const ERROR_NAMES = [
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

    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->init();
    }

    public function register($request): void
    {
        set_exception_handler(fn (Throwable $e) => $this->handleException($e, $request));
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'handleFatalError'], $request);
    }

    public function handleException(Throwable $e, $request): void
    {
        $this->unregister();

        $this->log($e, $request);

        $this->send($this->renderException($e, $request));
        exit(1);
    }

    public function handleError(int $severity, string $message, string $file, int $line): void
    {
        if (!(error_reporting() & $severity)) {
            return;
        }

        throw new ErrorException($message, $severity, $severity, $file, $line);
    }

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

    public function getErrorName(int $severity): string
    {
        return self::ERROR_NAMES[$severity] ?? 'Error';
    }

    protected function convertToString(Throwable $e): string
    {
        return "Exception '" . get_class($e) . "' with message '{$e->getMessage()}' \n\nin "
            . $e->getFile() . ':' . $e->getLine() . "\n\n"
            . "Stack trace:\n" . $e->getTraceAsString();
    }

    private function isFatalError(array $error): bool
    {
        return isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING], true);
    }

    private function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    private function send($data)
    {
        if (is_string($data)) {
            echo $data;
        } elseif ($data instanceof ResponseInterface) {
            (new SapiEmitter())->emit($data);
        }
    }

    abstract protected function init(): void;

    abstract protected function renderException(Throwable $e, $request = null);

    abstract protected function log(Throwable $e, $request): void;
}
