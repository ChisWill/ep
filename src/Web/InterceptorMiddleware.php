<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Contract\InterceptorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class InterceptorMiddleware implements MiddlewareInterface
{
    private array $includePath = [];
    private array $excludePath = [];

    public function __construct(InterceptorInterface $interceptor = null)
    {
        if ($interceptor === null) {
            return;
        }
        $baseUrl = Ep::getConfig()->baseUrl;
        $this->includePath = $interceptor->includePath();
        $this->excludePath = $interceptor->excludePath();
        foreach ($this->includePath as [&$path, $callback]) {
            $path = '/' . ltrim($baseUrl . $path, '/');
        }
        foreach ($this->excludePath as [&$path, $callback]) {
            $path = '/' . ltrim($baseUrl . $path, '/');
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        foreach ($this->includePath as [$path, $callback]) {
            if (strpos($uri, $path) === 0) {
                $result = $callback($request);
                if ($result !== true) {
                    return $result;
                }
            }
        }
        foreach ($this->excludePath as [$path, $callback]) {
            if (strpos($uri, $path) !== 0) {
                $result = $callback($request);
                if ($result !== true) {
                    return $result;
                }
            }
        }
        return $handler->handle($request);
    }
}
