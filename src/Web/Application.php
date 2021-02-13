<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Standard\RouteInterface;
use Ep\Standard\ServerRequestFactoryInterface;
use Yiisoft\Http\Method;
use Yiisoft\Yii\Web\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Application extends \Ep\Base\Application
{
    /**
     * {@inheritDoc}
     */
    protected function register(): void
    {
        Ep::getDi()->get(ErrorHandler::class)->register();
    }

    /**
     * {@inheritDoc}
     */
    protected function handle(): void
    {
        $request = $this->createRequest();
        $response = $this->handleRequest($request);
        if ($response instanceof ResponseInterface) {
            (new SapiEmitter)->emit($response, $request->getMethod() === Method::HEAD);
        }
    }

    private function createRequest(): ServerRequestInterface
    {
        return Ep::getDi()
            ->get(ServerRequestFactoryInterface::class)
            ->createFromGlobals();
    }

    private function handleRequest(ServerRequestInterface $request): ?ResponseInterface
    {
        $config = Ep::getConfig();
        $route = Ep::getDi()
            ->get(RouteInterface::class)
            ->setConfig([
                'config' => $config,
                'baseUrl' => $config->baseUrl,
                'controllerSuffix' => $config->controllerDirAndSuffix
            ]);
        [$handler, $params] = $route->solveRouteInfo(
            $route->matchRequest(
                $request->getUri()->getPath(),
                $request->getMethod()
            )
        );
        $request = $request->withQueryParams($params);
        [$controller, $action] = $route->parseHandler($handler);
        return $route
            ->createController($controller)
            ->run($action, $request);
    }
}
