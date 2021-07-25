<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use Ep\Tests\Support\Middleware\AddMiddleware;
use Ep\Tests\Support\Middleware\FilterMiddleware;
use Ep\Web\Module as WebModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Module extends WebModule
{
    public function __construct()
    {
        $this->setMiddlewares([
            AddMiddleware::class,
            FilterMiddleware::class,
        ]);
    }

    public function before(ServerRequestInterface $request)
    {
        return true;
    }

    public function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}
