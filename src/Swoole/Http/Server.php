<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Closure;
use Ep\Swoole\Config;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Contract\ServerTrait;
use Ep\Swoole\Http\Emitter;
use Ep\Swoole\Http\ServerRequest;
use Ep\Swoole\SwooleEvent;
use Ep\Web\Application as WebApplication;
use Ep\Web\ErrorHandler;
use Ep\Web\Service;
use Yiisoft\Http\Method;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as HttpServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class Server implements ServerInterface
{
    use ServerTrait;

    private WebApplication $webApplication;
    private ServerRequest $serverRequest;
    private ErrorHandler $errorHandler;
    private Service $service;

    public function __construct(
        WebApplication $webApplication,
        ServerRequest $serverRequest,
        ErrorHandler $errorHandler,
        Service $service
    ) {
        $this->webApplication = $webApplication;
        $this->serverRequest = $serverRequest;
        $this->errorHandler = $errorHandler;
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    protected function getServerClass(): string
    {
        return HttpServer::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function onRequest(): void
    {
        $this->getServer()->on(SwooleEvent::ON_REQUEST, [$this, 'handleRequest']);
    }

    public function handleRequest(Request $request, Response $response): void
    {
        try {
            $serverRequest = $this->serverRequest->create($request);

            $this->send(
                $serverRequest,
                $response,
                $this->webApplication->handleRequest($serverRequest)
            );
        } catch (Throwable $e) {
            try {
                $response->end($this->errorHandler->renderException($e, $serverRequest));
            } catch (Throwable $e) {
                $response->end($e->getMessage());
            }
        }
    }

    /**
     * @param mixed $result
     */
    private function send(ServerRequestInterface $request, Response $response, $result): void
    {
        if ($result instanceof ResponseInterface) {
            (new Emitter($response))->emit($result, $request->getMethod() === Method::HEAD);
        } else {
            if (is_string($result)) {
                $this->send($request, $response, $this->service->string($result));
            } elseif (is_array($result)) {
                $this->send($request, $response, $this->service->json($result));
            } else {
                $response->end();
            }
        }
    }
}
