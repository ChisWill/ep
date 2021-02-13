<?php

declare(strict_types=1);

namespace Ep\Console;

use Throwable;

class ErrorHandler extends \Ep\Base\ErrorHandler
{
    protected function init(): void
    {
    }

    public function renderException(Throwable $e): string
    {
        return $this->convertToString($e);
    }
}
