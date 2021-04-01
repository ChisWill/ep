<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\Application as BaseApplication;
use Ep\Base\Config;
use Ep\Base\ControllerRunner;
use Ep\Base\ErrorHandler;
use Ep\Base\Route;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\NotFoundException;

final class Application extends BaseApplication
{
    private Config $config;
    private ConsoleRequestInterface $consoleRequest;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;
    private Route $route;
    private ControllerRunner $controllerRunner;

    public function __construct(
        ConsoleRequestInterface $consoleRequest,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer,
        Route $route,
        ControllerRunner $controllerRunner
    ) {
        $this->config = Ep::getConfig();
        $this->consoleRequest = $consoleRequest;
        $this->errorHandler = $errorHandler;
        $this->errorRenderer = $errorRenderer;
        $this->route = $route;
        $this->controllerRunner = $controllerRunner;
    }

    public function createRequest(): ConsoleRequestInterface
    {
        return $this->consoleRequest;
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    public function register($request): void
    {
        $this->errorHandler
            ->configure([
                'errorRenderer' => $this->errorRenderer
            ])
            ->register($request);
    }

    /**
     * @param ConsoleRequestInterface $request
     * 
     * @return mixed
     */
    public function handleRequest($request)
    {
        try {
            [, $handler] = $this->route
                ->configure(['baseUrl' => '/'])
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
     * @param ConsoleRequestInterface $request
     * @param mixed                   $response
     */
    public function send($request, $response): void
    {
        if (is_scalar($response)) {
            echo $response;
        } elseif (is_array($response)) {
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
}
