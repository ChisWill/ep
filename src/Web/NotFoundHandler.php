<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Base\ControllerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFoundHandler implements RequestHandlerInterface
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $config = Ep::getConfig();

        $factory = new ControllerFactory($config->controllerDirAndSuffix);
        return $factory->run($config->notFoundHandler, $request);
    }
}
