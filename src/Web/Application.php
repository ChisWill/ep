<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\Config;
use Ep\Base\ErrorHandler;
use Ep\Contract\NotFoundHandlerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Yii\Web\SapiEmitter;
use Yiisoft\Yii\Web\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Application
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

    public function run(): void
    {
        $request = $this->createRequest();

        $this->register($request);

        $this->send($request, $this->handleRequest($request));
    }

    private function createRequest(): ServerRequestInterface
    {
        return new ServerRequest($this->serverRequestFactory->createFromGlobals());
    }

    private function register(ServerRequestInterface $request): void
    {
        $this->errorHandler->register($request);
    }

    private function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->requestHandlerFactory
            ->wrap($this->config->webMiddlewares, $this->notFoundHandler)
            ->handle($request);
    }

    private function send(ServerRequestInterface $request, ResponseInterface $response): void
    {
        $this->sapiEmitter->emit(
            $response,
            $request->getMethod() === Method::HEAD
        );
    }
}
