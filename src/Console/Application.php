<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\Config;
use Ep\Base\ControllerRunner;
use Ep\Base\ErrorHandler;
use Ep\Base\Route;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\NotFoundException;
use Symfony\Component\Console\Application as SymfonyApplication;

final class Application
{
    private Config $config;
    private SymfonyApplication $symfonyApplication;
    private ConsoleRequestInterface $consoleRequest;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;
    private Route $route;
    private ControllerRunner $controllerRunner;

    public function __construct(
        Config $config,
        SymfonyApplication $symfonyApplication,
        ConsoleRequestInterface $consoleRequest,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer,
        Route $route,
        ControllerRunner $controllerRunner
    ) {
        $this->config = $config;
        $this->symfonyApplication = $symfonyApplication;
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

        $this->send($request, $this->handleRequest($request));
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
    private function handleRequest(ConsoleRequestInterface $request)
    {
        try {
            [, $handler] = $this->route
                ->configure([
                    'rule' => $this->config->getRoute(),
                    'baseUrl' => '/'
                ])
                ->match($request->getRoute());

            return $this->controllerRunner
                ->configure(['suffix' => $this->config->commandDirAndSuffix])
                ->run($handler, $request);
        } catch (NotFoundException $e) {
            $command = trim($request->getRoute(), '/');
            echo <<<HELP
Error: unknown command "{$command}"

HELP;
            exit(1);
        }
    }

    /**
     * @param mixed $response
     */
    private function send(ConsoleRequestInterface $request, $response): void
    {
        if (is_scalar($response)) {
            if ($response) {
                echo $response . PHP_EOL;
            }
        } elseif (is_array($response)) {
            foreach ($response as $row) {
                $this->send($request, $row);
            }
        }
    }
}
