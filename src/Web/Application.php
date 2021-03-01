<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Application as BaseApplication;
use Ep\Base\ErrorHandler;
use Ep\Contract\NotFoundHandlerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\Yii\Web\SapiEmitter;
use Yiisoft\Yii\Web\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Application extends BaseApplication
{
    private ServerRequestFactory $serverRequestFactory;
    private ErrorHandler $errorHandler;
    private MiddlewareDispatcher $middlewareDispatcher;
    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(
        ServerRequestFactory $serverRequestFactory,
        ErrorHandler $errorHandler,
        MiddlewareDispatcher $middlewareDispatcher,
        NotFoundHandlerInterface $notFoundHandler
    ) {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->errorHandler = $errorHandler;
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->notFoundHandler = $notFoundHandler;
    }

    public function createRequest(): ServerRequestInterface
    {
        return $this->serverRequestFactory->createFromGlobals();
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function register($request): void
    {
        $this->errorHandler->register($request);
    }

    /**
     * @param  ServerRequestInterface $request
     * 
     * @return mixed
     */
    public function handleRequest($request)
    {
        return $this
            ->middlewareDispatcher
            ->dispatch(
                $request,
                $this->notFoundHandler
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
