<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\ErrorHandler;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ErrorRendererInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class ErrorRenderer implements ErrorRendererInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ConsoleRequestInterface|null $request
     */
    public function render(Throwable $t, $request = null): string
    {
        return ErrorHandler::convertToString($t);
    }

    /**
     * @param ConsoleRequestInterface|null $request
     */
    public function log(Throwable $t, $request = null): void
    {
        $context = [
            'category' => self::class
        ];
        if ($request) {
            $context['route'] = $request->getRoute();
            $context['params'] = $request->getParams();
        }

        $this->logger->error(ErrorHandler::convertToString($t), $context);
    }
}
