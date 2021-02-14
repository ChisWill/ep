<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ControllerFactory;
use Ep\Base\Route;
use Ep\Standard\ConsoleRequestInterface;

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
        [$handler] = (new Route)->match($request->getRoute());
        (new ControllerFactory)->run($handler, $request);
    }
}
