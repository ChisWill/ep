<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\Config;
use Ep\Base\ControllerFactory;
use Ep\Base\ErrorHandler;
use Ep\Base\Route;
use Ep\Contract\ConsoleRequestInterface;
use UnexpectedValueException;

final class Application extends \Ep\Base\Application
{
    private Config $config;
    private ConsoleRequestInterface $consoleRequest;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;
    private Route $route;
    private ControllerFactory $controllerFactory;

    public function __construct(
        ConsoleRequestInterface $consoleRequest,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer,
        Route $route,
        ControllerFactory $controllerFactory
    ) {
        $this->config = Ep::getConfig();
        $this->consoleRequest = $consoleRequest;
        $this->errorHandler = $errorHandler;
        $this->errorRenderer = $errorRenderer;
        $this->route = $route;
        $this->controllerFactory = $controllerFactory;
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
            [$handler] = $this->route
                ->configure(['baseUrl' => '/'])
                ->match($request->getRoute());

            return $this->controllerFactory
                ->configure(['suffix' => $this->config->commandDirAndSuffix])
                ->run($handler, $request);
        } catch (UnexpectedValueException $e) {
            $command = trim($handler, '/');
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
