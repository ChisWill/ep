<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Middleware;

use Ep;
use Ep\Tests\Support\RequestHandler\ErrorHandler;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class CheckMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $attributes = $request->getAttributes();
        try {
            $age = $attributes['age'] ?? 0;
            if ($age >= 100) {
                throw new Exception('You are died.');
            } elseif ($age == 0) {
                throw new Exception('You haven\'t been born yet.');
            }
            return $handler->handle($request);
        } catch (Throwable $t) {
            $request = $request->withParsedBody([$t->getMessage()]);
            return Ep::getDi()->get(ErrorHandler::class)->handle($request);
        }
    }
}
