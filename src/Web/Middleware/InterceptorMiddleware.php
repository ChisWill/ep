<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep\Base\Config;
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
        Config $config,
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

        foreach ($interceptor->includePath() as $path => $class) {
            $this->includePath['/' . trim($config->baseUrl . $path, '/')] = (array) $class;
        }
        foreach ($interceptor->excludePath() as $path => $class) {
            $this->excludePath['/' . trim($config->baseUrl . $path, '/')] = (array) $class;
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestPath = $request->getUri()->getPath();
        $classList = [];
        foreach ($this->includePath as $path => $class) {
            if (strpos($requestPath, $path) === 0) {
                $classList = array_merge($classList, $class);
            }
        }
        foreach ($this->excludePath as $path => $class) {
            if (strpos($requestPath, $path) !== 0) {
                $classList = array_merge($classList, $class);
            }
        }

        $stack = [];
        $middlewares = [];
        foreach ($classList as $class) {
            /** @var FilterInterface */
            $filter = $this->container->get($class);
            $result = $filter->before($request);
            if ($result === true) {
                $stack[] = $filter;
                $middlewares = array_merge($middlewares, $filter->getMiddlewares());
            } elseif ($result instanceof ResponseInterface) {
                return $result;
            } else {
                return $this->service->status(Status::NOT_ACCEPTABLE);
            }
        }

        if ($middlewares) {
            $response = $this->requestHandlerFactory
                ->wrap($middlewares, $handler)
                ->handle($request);
        } else {
            $response = $handler->handle($request);
        }

        while ($filter = array_pop($stack)) {
            /** @var FilterInterface $filter */
            $response = $filter->after($request, $response);
        }

        return $response;
    }
}
