<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MultipleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $attributes = $request->getAttributes();
        foreach ($attributes as $name => $value) {
            if (is_numeric($value)) {
                $request = $request->withAttribute($name, $value * 2);
            }
        }

        return $handler->handle($request);
    }
}
