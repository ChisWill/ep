<?php

declare(strict_types=1);

namespace Ep\Tests\App\Facade;

use Ep\Base\Facade;
use Psr\Log\LoggerInterface;

final class Logger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LoggerInterface::class;
    }
}
