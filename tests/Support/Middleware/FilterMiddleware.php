<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FilterMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $query = $request->getQueryParams();
        $query['sex'] ??= 21;
        $query['age'] ??= 10;
        $query['age'] += 150;
        $request = $request->withQueryParams($query);
        return $handler->handle($request);
    }
}
