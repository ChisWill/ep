<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\Config;
use Ep\Base\ErrorHandler;
use Ep\Base\Route;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\NotFoundException;

final class Application
{
    private Config $config;
    private Factory $factory;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;
    private Route $route;
    private ControllerRunner $controllerRunner;

    public function __construct(
        Config $config,
        Factory $factory,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer,
        Route $route,
        ControllerRunner $controllerRunner
    ) {
        $this->config = $config;
        $this->factory = $factory;
        $this->errorHandler = $errorHandler;
        $this->errorRenderer = $errorRenderer;
        $this->route = $route;
        $this->controllerRunner = $controllerRunner;
    }

    public function run(): void
    {
        $request = $this->createRequest();

        $this->register($request);

        $this->handleRequest($request);
    }

    private ?ConsoleRequestInterface $request = null;

    public function withRequest(ConsoleRequestInterface $request): self
    {
        $new = clone $this;
        $new->request = $request;
        return $new;
    }

    public function createRequest(): ConsoleRequestInterface
    {
        return $this->request ?? $this->factory->createRequest();
    }

    public function register(ConsoleRequestInterface $request): void
    {
        $this->errorHandler
            ->configure([
                'errorRenderer' => $this->errorRenderer
            ])
            ->register($request);
    }

    public function handleRequest(ConsoleRequestInterface $request): void
    {
        try {
            $route = $request->getRoute();

            [, $handler] = $this->route
                ->configure([
                    'rule' => $this->config->getRouteRule(),
                    'baseUrl' => '/'
                ])
                ->match('/' . $route);

            $code = $this->controllerRunner
                ->configure(['suffix' => $this->config->commandDirAndSuffix])
                ->run($handler, $request);

            exit($code);
        } catch (NotFoundException $e) {
            echo <<<HELP
Error: unknown command "{$route}"\n
HELP;
            exit(Command::FAIL);
        }
    }
}
