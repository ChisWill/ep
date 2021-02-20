<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Ep\Swoole\Config;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Http\Emitter;
use Ep\Swoole\Http\ServerRequest;
use Ep\Web\Application as WebApplication;
use Ep\Web\ErrorHandler;
use Ep\Web\Service;
use Yiisoft\Http\Method;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Server as SwooleHttpServer;
use Throwable;

final class Server implements ServerInterface
{
    private SwooleHttpServer $server;

    private array $config;
    private WebApplication $webApplication;
    private ServerRequest $serverRequest;
    private ErrorHandler $errorHandler;
    private Service $service;

    public function __construct(
        array $config,
        WebApplication $webApplication,
        ServerRequest $serverRequest,
        ErrorHandler $errorHandler,
        Service $service
    ) {
        $this->config = $config;
        $this->webApplication = $webApplication;
        $this->serverRequest = $serverRequest;
        $this->errorHandler = $errorHandler;
        $this->service = $service;

        $this->init();
    }

    private function init(): void
    {
        $this->server = new SwooleHttpServer(
            $this->config['host'] ?? '127.0.0.1',
            $this->config['port'] ?? null,
            $this->config['mode'] ?? null,
            $this->config['sockType'] ?? null,
        );
        $this->server->set($this->config['settings'] ?? []);
        $this->server->on('request', [$this, 'handlerRequest']);
    }

    public function start(): void
    {
        $this->server->start();
    }

    public function listen(string $host, int $port, int $socketType): ServerInterface
    {
        $port = $this->server->listen($host, $port, $socketType);
        return $this;
    }

    public function handlerRequest(Request $request, Response $response): void
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
