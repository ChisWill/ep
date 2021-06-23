<?php

declare(strict_types=1);

namespace Ep\Tests\App\Facade;

use Ep\Base\Facade;
use Psr\SimpleCache\CacheInterface;

final class Cache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CacheInterface::class;
    }
}
