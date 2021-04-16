<?php

declare(strict_types=1);

namespace Ep\Web\Middleware;

use Ep\Base\Config;
use Ep\Base\Route;
use Ep\Contract\NotFoundException;
use Ep\Web\ControllerRunner;
use Ep\Web\Service;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouteMiddleware implements MiddlewareInterface
{
    private Config $config;
    private Route $route;
    private ControllerRunner $controllerRunner;
    private Service $service;

    public function __construct(
        Config $config,
        Route $route,
        ControllerRunner $controllerRunner,
        Service $service
    ) {
        $this->config = $config;
        $this->route = $route;
        $this->controllerRunner = $controllerRunner;
        $this->service = $service;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            [$allowed, $result, $params] = $this->route
                ->clone([
                    'rule' => $this->config->getRoute(),
                    'baseUrl' => $this->config->baseUrl
                ])
                ->match(
                    $request->getUri()->getPath(),
                    $request->getMethod()
                );
            if (!$allowed) {
                return $this->service->status(Status::METHOD_NOT_ALLOWED);
            }

            foreach ($params as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }

            return $this->service->toResponse($this->controllerRunner->run($result, $request));
        } catch (NotFoundException $e) {
            return $handler->handle($request->withAttribute('exception', $e));
        }
    }
}
