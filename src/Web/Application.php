<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Contract\NotFoundHandlerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\Yii\Web\SapiEmitter;
use Yiisoft\Yii\Web\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        return Ep::getDi()
            ->get(
                MiddlewareDispatcher::class
            )
            ->withMiddlewares(
                Ep::getConfig()->httpMiddlewares
            )
            ->dispatch(
                $request,
                Ep::getDi()->get(NotFoundHandlerInterface::class)
            );
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function send($request, $response): void
    {
        (new SapiEmitter())->emit(
            $response,
            $request->getMethod() === Method::HEAD
        );
    }
}
