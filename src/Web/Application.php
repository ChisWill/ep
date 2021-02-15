<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Base\ControllerFactory;
use Ep\Base\Route;
use Ep\Contract\ServerRequestFactoryInterface;
use Yiisoft\Http\Method;
use Yiisoft\Yii\Web\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class Application extends \Ep\Base\Application
{
    /**
     * {@inheritDoc}
     */
    protected function createRequest(): ServerRequestInterface
    {
        return Ep::getDi()
            ->get(ServerRequestFactoryInterface::class)
            ->createFromGlobals();
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function register($request): void
    {
        Ep::getDi()->get(ErrorHandler::class)->register($request);
    }

    /**
     * @param ServerRequestInterface $request
     */
    protected function handleRequest($request): void
    {
        $config = Ep::getConfig();

        [$handler, $params] = (new Route($config->baseUrl))->match(
            $request->getUri()->getPath(),
            $request->getMethod()
        );
        $request = $request->withQueryParams($params);

        $factory = new ControllerFactory($config->controllerDirAndSuffix);
        try {
            $response = $factory->run($handler, $request);
        } catch (RuntimeException $e) {
            $response = $factory->run($config->notFoundHandler, $request);
        }
        if ($response instanceof ResponseInterface) {
            (new SapiEmitter)->emit($response, $request->getMethod() === Method::HEAD);
        }
    }
}
