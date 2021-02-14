<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Standard\ConsoleRequestInterface;
use Throwable;

class ErrorHandler extends \Ep\Base\ErrorHandler
{
    /**
     * @param ConsoleRequestInterface|null $request
     */
    public function renderException(Throwable $e, $request = null): string
    {
        return $this->convertToString($e);
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    protected function log(Throwable $e, $request): void
    {
        $context = [
            'category' => 'exception',
            'route' => $request->getRoute(),
            'params' => $request->getParams()
        ];

        $this->logger->error($this->convertToString($e), $context);
    }
}
