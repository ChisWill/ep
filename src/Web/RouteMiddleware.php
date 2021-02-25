<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Config;
use Ep\Base\ControllerFactory;
use Ep\Base\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteMiddleware implements MiddlewareInterface
{
    private Route $route;
    private ControllerFactory $controllerFactory;

    public function __construct(Config $config, Route $route, ControllerFactory $controllerFactory)
    {
        $this->route = $route->clone([
            'rule' => $config->getRoute(),
            'baseUrl' => $config->baseUrl
        ]);
        $this->controllerFactory = $controllerFactory->clone([
            'suffix' => $config->controllerDirAndSuffix
        ]);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
        [$handler, $params] = $this->route->match(
            $request->getUri()->getPath(),
            $request->getMethod()
        );

        foreach ($params as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        try {
            return $this->controllerFactory->run($handler, $request);
        } catch (UnexpectedValueException $e) {
            return $requestHandler->handle($request);
        }
    }
}
