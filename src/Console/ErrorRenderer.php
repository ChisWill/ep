<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\ErrorRenderer as BaseErrorRenderer;
use Symfony\Component\Console\Input\InputInterface;
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
     * @param InputInterface $request
     */
    public function log(Throwable $t, $request): void
    {
        $context = [
            'category' => self::class
        ];
        if ($request) {
            $context['route'] = $request->getFirstArgument();
            $context['arguments'] = $request->getArguments();
            $context['options'] = $request->getOptions();
        }

        $this->logger->error($this->render($t, $request), $context);
    }
}
