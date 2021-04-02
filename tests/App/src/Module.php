<?php

declare(strict_types=1);

namespace Ep\Tests\App;

use Ep\Base\Module as BaseModule;
use Ep\Tests\Support\Middleware\AddMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;

final class Module extends BaseModule
{
    public function __construct()
    {
        $this->setMiddlewares([
            FilterMiddleware::class,
            AddMiddleware::class
        ]);
    }
}
