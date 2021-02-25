<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Base\ControllerFactory;
use Ep\Base\Route;
use Yiisoft\Http\Method;
use Yiisoft\Yii\Web\SapiEmitter;
use Yiisoft\Yii\Web\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

final class Application extends \Ep\Base\Application
{
    /**
     * @return ServerRequestInterface
     */
    public function createRequest(): ServerRequestInterface
    {
        return Ep::getDi()
            ->get(ServerRequestFactory::class)
            ->createFromGlobals();
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function register($request): void
    {
        Ep::getDi()->get(ErrorHandler::class)->register($request);
    }

    /**
     * @param  ServerRequestInterface $request
     * 
     * @return mixed
     */
    public function handleRequest($request)
    {
        $config = Ep::getConfig();

        [$handler, $params] = (new Route($config->getRoute(), $config->baseUrl))->match(
            $request->getUri()->getPath(),
            $request->getMethod()
        );
        $request = $request->withQueryParams($params);

        $factory = new ControllerFactory($config->controllerDirAndSuffix);
        try {
            return $factory->run($handler, $request);
        } catch (UnexpectedValueException $e) {
            return $factory->run($config->notFoundHandler, $request);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param mixed                  $response
     */
    public function send($request, $response): void
    {
        if ($response instanceof ResponseInterface) {
            (new SapiEmitter())->emit($response, $request->getMethod() === Method::HEAD);
        } else {
            $service = Ep::getDi()->get(Service::class);
            if (is_string($response)) {
                $this->send($request, $service->string($response));
            } elseif (is_array($response)) {
                $this->send($request, $service->json($response));
            }
        }
    }
}
