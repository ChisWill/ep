<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep;
use Ep\Contract\FilterInterface;
use Ep\Contract\InterceptorInterface;
use Ep\Web\RequestHandlerFactory;
use Ep\Web\Service;
use Yiisoft\Http\Status;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class InterceptorMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;
    private RequestHandlerFactory $requestHandlerFactory;
    private Service $service;
    private array $includePath = [];
    private array $excludePath = [];

    public function __construct(
        ContainerInterface $container,
        RequestHandlerFactory $requestHandlerFactory,
        Service $service,
        InterceptorInterface $interceptor = null
    ) {
        if ($interceptor === null) {
            return;
        }
        $this->container = $container;
        $this->requestHandlerFactory = $requestHandlerFactory;
        $this->service = $service;
        $this->includePath = $interceptor->includePath();
        $this->excludePath = $interceptor->excludePath();
        $baseUrl = Ep::getConfig()->baseUrl;

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
        $middlewares = [];
        $uri = $request->getUri()->getPath();

        foreach ($this->includePath as [$path, $class]) {
            if (strpos($uri, $path) === 0) {
                $result = $this->before($class, $request, $stack, $middlewares);
                if ($result instanceof ResponseInterface) {
                    return $result;
                }
            }
        }
        foreach ($this->excludePath as [$path, $class]) {
            if (strpos($uri, $path) !== 0) {
                $result = $this->before($class, $request, $stack, $middlewares);
                if ($result instanceof ResponseInterface) {
                    return $result;
                }
            }
        }

        if ($middlewares) {
            krsort($middlewares);
            $middlewares = array_unique($middlewares);
            ksort($middlewares);
            $response = $this->requestHandlerFactory
                ->wrap($middlewares, $handler)
                ->handle($request);
        } else {
            $response = $handler->handle($request);
        }

        /** @var FilterInterface $filter */
        while ($filter = array_pop($stack)) {
            $response = $filter->after($request, $response);
        }

        return $response;
    }

    /**
     * @return true|ResponseInterface
     */
    private function before(string $class, ServerRequestInterface $request, array &$stack, array &$middlewares)
    {
        /** @var FilterInterface $filter */
        $filter = $this->container->get($class);
        $result = $filter->before($request);
        if ($result === true || $result instanceof ResponseInterface) {
            $stack[] = $filter;
            $middlewares = array_merge($filter->getMiddlewares(), $middlewares);
            return $result;
        } else {
            return $this->service->status(Status::NOT_ACCEPTABLE);
        }
    }
}
