<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Standard\ConsoleRequestInterface;
use Ep\Standard\RouteInterface;

final class Application extends \Ep\Base\Application
{
    /**
     * @inheritDoc
     */
    protected function handle(): int
    {
        $this->handleRequest($this->createRequest());
        return 0;
    }

    private function createRequest(): ConsoleRequestInterface
    {
        return Ep::getDi()->get(ConsoleRequestInterface::class);
    }

    private function handleRequest(ConsoleRequestInterface $request)
    {
        $config = Ep::getConfig();
        $route = Ep::getDi()
            ->get(RouteInterface::class)
            ->setConfig([
                'config' => $config,
                'baseUrl' => '/',
                'controllerSuffix' => $config->commandDirAndSuffix
            ]);
        [$handler] = $route->solveRouteInfo(
            $route->matchRequest(
                $request->getRoute()
            )
        );
        [$controller, $action] = $route->parseHandler($handler);
        return $route
            ->createController($controller)
            ->setConfig([
                'config' => $config
            ])
            ->run($action, $request);
    }
}
