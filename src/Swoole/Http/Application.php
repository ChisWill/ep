<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Ep\Swoole\Config;
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
use Throwable;

final class Application
{
    private Config $config;
    private WebApplication $webApplication;
    private ServerRequest $serverRequest;
    private ErrorHandler $errorHandler;
    private Service $service;

    public function __construct(
        Config $config,
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
    }

    public function run()
    {
        \Swoole\Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

        \Swoole\Coroutine\run(function () {
            $server = new \Swoole\Coroutine\Http\Server($this->config->httpHost, $this->config->httpPort, $this->config->httpSSL);

            $server->handle('/', [$this, 'handlerRequest']);

            $server->start();
        });
    }

    public function handlerRequest(Request $request, Response $response): void
    {
        try {
            $serverRequest = $this->serverRequest->create($request);

            $result = $this->webApplication->handleRequest($serverRequest);

            $this->send($serverRequest, $response, $result);
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
