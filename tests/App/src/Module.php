<?php

declare(strict_types=1);

namespace Ep\Tests\App;

use Ep\Base\Module as BaseModule;
use Ep\Tests\Support\Middleware\AddMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;
use Ep\Tests\Support\Middleware\InitMiddleware;
use Ep\Web\ServerRequest;
use Psr\Http\Message\ResponseInterface;

final class Module extends BaseModule
{
    private array $middlewares = [];

    public function __construct()
    {
        $this->setMiddlewares([
            FilterMiddleware::class,
            AddMiddleware::class,
            InitMiddleware::class
        ]);
    }
}
