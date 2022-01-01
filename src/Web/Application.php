<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\ErrorHandler;
use Ep\Contract\NotFoundHandlerInterface;
use Ep\Web\Middleware\InterceptorMiddleware;
use Ep\Web\Middleware\RouteMiddleware;
use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\ServerRequest\ServerRequestCreator;
use Yiisoft\Http\Method;
use Yiisoft\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Application
{
    private ServerRequestCreator $serverRequestCreator;
    private RequestHandlerFactory $requestHandlerFactory;
    private SapiEmitter $sapiEmitter;
    private ErrorRenderer $errorRenderer;
    private NotFoundHandlerInterface $notFoundHandler;

    public function __construct(
        ServerRequestCreator $serverRequestCreator,
        RequestHandlerFactory $requestHandlerFactory,
        SapiEmitter $sapiEmitter,
        ErrorRenderer $errorRenderer,
        NotFoundHandlerInterface $notFoundHandler
    ) {
        $this->serverRequestCreator = $serverRequestCreator;
        $this->requestHandlerFactory = $requestHandlerFactory;
        $this->sapiEmitter = $sapiEmitter;
        $this->errorRenderer = $errorRenderer;
        $this->notFoundHandler = $notFoundHandler;
    }

    private array $middlewares = [
        InterceptorMiddleware::class,
        SessionMiddleware::class,
        RouteMiddleware::class
    ];

    public function withMiddlewares(array $middlewares): self
    {
        $new = clone $this;
        $new->middlewares = $middlewares;
        return $new;
    }

    public function run(): void
    {
        $request = $this->createRequest();

        $this->register($request);

        $this->emit($request, $this->handleRequest($request));
    }

    public function createRequest(): ServerRequestInterface
    {
        return new ServerRequest($this->serverRequestCreator->createFromGlobals());
    }

    public function register(ServerRequestInterface $request): void
    {
        (new ErrorHandler($this->errorRenderer))->register($request);
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        return $this->requestHandlerFactory
            ->wrap($this->middlewares, $this->notFoundHandler)
            ->handle($request);
    }

    public function emit(ServerRequestInterface $request, ResponseInterface $response): void
    {
        $this->sapiEmitter->emit(
            $response,
            $request->getMethod() === Method::HEAD
        );
    }
}
