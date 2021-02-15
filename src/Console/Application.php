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
     * {@inheritDoc}
     */
    protected function createRequest(): ConsoleRequestInterface
    {
        return Ep::getDi()->get(ConsoleRequestInterface::class);
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    protected function register($request): void
    {
        Ep::getDi()->get(ErrorHandler::class)->register($request);
    }

    /**
     * @param ConsoleRequestInterface $request
     */
    protected function handleRequest($request): void
    {
        $config = Ep::getConfig();

        [$handler] = (new Route($config->baseUrl))->match($request->getRoute());

        try {
            (new ControllerFactory($config->commandDirAndSuffix))->run($handler, $request);
        } catch (RuntimeException $e) {
            $command = trim($handler, '/');
            echo <<<HELP
Error: unknown command "{$command}"
HELP;
            exit(1);
        }
    }
}
