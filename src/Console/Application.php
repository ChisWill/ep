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
    private ConsoleRequestInterface $request;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;
    private Route $route;
    private ControllerRunner $controllerRunner;

    public function __construct(
        Config $config,
        ConsoleRequestInterface $request,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer,
        Route $route,
        ControllerRunner $controllerRunner
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->errorRenderer = $errorRenderer;
        $this->route = $route;
        $this->controllerRunner = $controllerRunner;
    }

    public function run(): void
    {
        $this->register();

        $this->handleRequest();
    }

    private function register(): void
    {
        $this->errorHandler
            ->configure([
                'errorRenderer' => $this->errorRenderer
            ])
            ->register($this->request);
    }

    private function handleRequest(): void
    {
        try {
            [, $handler] = $this->route
                ->configure([
                    'rule' => $this->config->getRouteRule(),
                    'baseUrl' => '/'
                ])
                ->match('/' . $this->request->getRoute());

            $response = $this->controllerRunner
                ->configure(['suffix' => $this->config->commandDirAndSuffix])
                ->run($handler, $this->request);

            exit($response->getCode());
        } catch (NotFoundException $e) {
            $command = $this->request->getRoute();
            echo <<<HELP
Error: unknown command "{$command}"\n
HELP;
            exit(Command::FAIL);
        }
    }
}
