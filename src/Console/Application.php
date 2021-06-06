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
    private ConsoleRequestInterface $consoleRequest;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;
    private Route $route;
    private ControllerRunner $controllerRunner;

    public function __construct(
        Config $config,
        ConsoleRequestInterface $consoleRequest,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer,
        Route $route,
        ControllerRunner $controllerRunner
    ) {
        $this->config = $config;
        $this->consoleRequest = $consoleRequest;
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

    private function createRequest(): ConsoleRequestInterface
    {
        return $this->consoleRequest;
    }

    private function register(ConsoleRequestInterface $request): void
    {
        $this->errorHandler
            ->configure([
                'errorRenderer' => $this->errorRenderer
            ])
            ->register($request);
    }

    /** 
     * @return mixed
     */
    private function handleRequest(ConsoleRequestInterface $request): void
    {
        try {
            [, $handler] = $this->route
                ->configure([
                    'rule' => $this->config->getRouteRule(),
                    'baseUrl' => '/'
                ])
                ->match('/' . $request->getRoute());

            $code = $this->controllerRunner
                ->configure(['suffix' => $this->config->commandDirAndSuffix])
                ->run($handler, $request);

            exit($code);
        } catch (NotFoundException $e) {
            $command = $request->getRoute();
            echo <<<HELP
Error: unknown command "{$command}"

HELP;
            exit(Command::FAIL);
        }
    }
}
