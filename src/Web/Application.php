<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Application as BaseApplication;
use Ep\Base\Config;
use Ep\Base\ErrorHandler;
use Ep\Contract\NotFoundHandlerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Yii\Web\SapiEmitter;
use Yiisoft\Yii\Web\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Application extends BaseApplication
{
    private Config $config;
    private ServerRequestFactory $serverRequestFactory;
    private ErrorHandler $errorHandler;
    private RequestHandlerFactory $requestHandlerFactory;
    private NotFoundHandlerInterface $notFoundHandler;
    private SapiEmitter $sapiEmitter;

    public function __construct(
        Config $config,
        ServerRequestFactory $serverRequestFactory,
        ErrorHandler $errorHandler,
        RequestHandlerFactory $requestHandlerFactory,
        NotFoundHandlerInterface $notFoundHandler,
        SapiEmitter $sapiEmitter
    ) {
        $this->config = $config;
        $this->serverRequestFactory = $serverRequestFactory;
        $this->errorHandler = $errorHandler;
        $this->requestHandlerFactory = $requestHandlerFactory;
        $this->notFoundHandler = $notFoundHandler;
        $this->sapiEmitter = $sapiEmitter;
    }

    public function createRequest(): ServerRequestInterface
    {
        return new ServerRequest($this->serverRequestFactory->createFromGlobals());
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
     * @return ResponseInterface
     */
    public function handleRequest($request)
    {
        return $this->requestHandlerFactory
            ->wrap($this->config->webMiddlewares, $this->notFoundHandler)
            ->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function send($request, $response): void
    {
        $this->sapiEmitter->emit(
            $response,
            $request->getMethod() === Method::HEAD
        );
    }
}
