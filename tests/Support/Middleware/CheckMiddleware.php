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
        $query = $request->getQueryParams();
        try {
            $sex = $query['sex'] ?? 0;
            if ($sex == 1) {
                throw new Exception("sex could not be 1");
            }
            $age = $query['age'] ?? 60;
            if ($age < 50) {
                throw new Exception("age could not smaller than 50");
            }
            return $handler->handle($request);
        } catch (Throwable $e) {
            $request = $request->withParsedBody([$e->getMessage()]);
            return Ep::getDi()->get(ErrorHandler::class)->handle($request);
        }
    }
}
