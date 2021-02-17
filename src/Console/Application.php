<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ControllerFactory;
use Ep\Base\Route;
use Ep\Contract\ConsoleRequestInterface;
use RuntimeException;

final class Application extends \Ep\Base\Application
{
    /**
     * @return ConsoleRequestInterface
     */
    public function createRequest(): ConsoleRequestInterface
    {
        return Ep::getDi()->get(ConsoleRequestInterface::class);
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    public function register($request): void
    {
        Ep::getDi()->get(ErrorHandler::class)->register($request);
    }

    /**
     * @param ConsoleRequestInterface $request
     * 
     * @return mixed
     */
    public function handleRequest($request)
    {
        $config = Ep::getConfig();

        [$handler] = (new Route($config->baseUrl))->match($request->getRoute());

        try {
            return (new ControllerFactory($config->commandDirAndSuffix))->run($handler, $request);
        } catch (RuntimeException $e) {
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
        if (is_string($response)) {
            echo $response;
        }
    }
}
