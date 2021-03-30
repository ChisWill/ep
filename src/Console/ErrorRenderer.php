<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\ErrorRenderer as BaseErrorRenderer;
use Ep\Contract\ConsoleRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class ErrorRenderer extends BaseErrorRenderer
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    public function log(Throwable $t, $request): void
    {
        $context = [
            'category' => self::class
        ];
        if ($request) {
            $context['route'] = $request->getRoute();
            $context['params'] = $request->getParams();
        }

        $this->logger->error($this->render($t, $request), $context);
    }
}
