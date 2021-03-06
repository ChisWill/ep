<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep;
use Ep\Contract\FilterInterface;
use Ep\Contract\InterceptorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class InterceptorMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;
    private array $includePath = [];
    private array $excludePath = [];

    public function __construct(ContainerInterface $container, InterceptorInterface $interceptor = null)
    {
        if ($interceptor === null) {
            return;
        }
        $this->container = $container;
        $baseUrl = Ep::getConfig()->baseUrl;
        $this->includePath = $interceptor->includePath();
        $this->excludePath = $interceptor->excludePath();

        foreach ($this->includePath as [&$path, $class]) {
            $path = $baseUrl . $path;
        }
        foreach ($this->excludePath as [&$path, $class]) {
            $path = $baseUrl . $path;
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $stack = [];
        $uri = $request->getUri()->getPath();

        foreach ($this->includePath as [$path, $class]) {
            if (strpos($uri, $path) === 0) {
                $result = $this->invoke($class, $request, $stack);
                if ($result instanceof ResponseInterface) {
                    return $result;
                }
            }
        }
        foreach ($this->excludePath as [$path, $class]) {
            if (strpos($uri, $path) !== 0) {
                $result = $this->invoke($class, $request, $stack);
                if ($result instanceof ResponseInterface) {
                    return $result;
                }
            }
        }

        $response = $handler->handle($request);

        /** @var FilterInterface $filter */
        while ($filter = array_pop($stack)) {
            $response = $filter->after($request, $response);
        }

        return $response;
    }

    private function invoke(string $class, ServerRequestInterface $request, array &$stack)
    {
        /** @var FilterInterface $filter */
        $filter = $this->container->get($class);
        $stack[] = $filter;
        $result = $filter->before($request);
    }
}
