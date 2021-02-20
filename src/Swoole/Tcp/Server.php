<?php

declare(strict_types=1);

namespace Ep\Swoole\Tcp;

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
use Swoole\WebSocket\Frame;
use Throwable;

final class Server implements ServerInterface
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

    public function runAsync()
    {
        $server = new \Swoole\Server("0.0.0.0", 9502);

        $server->on('open', function (\Swoole\WebSocket\Server $server, $request) {
            // echo "server: handshake success with fd{$request->fd}\n";
        });

        $server->on('message', function (\Swoole\WebSocket\Server $server, Frame $frame) {
            $server->push($frame->fd, "Hello {$frame->data}!" . mt_rand());
        });

        $server->on('close', function ($ser, $fd) {
            // echo "client {$fd} closed\n";
        });

        $server->start();
    }

    public function run()
    {
        \Swoole\Coroutine::set([]);

        \Swoole\Coroutine\run(function () {
            $server = new \Swoole\Coroutine\Http\Server('127.0.0.1', 9502, false);

            $server->handle('/websocket', [$this, 'handlerRequest']);

            $server->start();
        });
    }

    public function handlerRequest(Request $request, Response $response): void
    {
        try {
            $response->upgrade();

            $serverRequest = $this->serverRequest->create($request);

            while (true) {
                /** @var \Swoole\WebSocket\Frame */
                $frame = $response->recv();
                if ($frame === '') {
                    $response->close();
                    break;
                } else if ($frame === false) {
                    echo 'errorCode: ' . swoole_last_error() . "\n";
                    $response->close();
                    break;
                } else {
                    echo $frame->data;
                    if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                        $response->close();
                        break;
                    } else {
                        $response->push("Hello {$frame->data}!" . mt_rand());
                    }
                }
            }
        } catch (Throwable $e) {
            echo $e->getMessage();
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
