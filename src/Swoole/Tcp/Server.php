<?php

declare(strict_types=1);

namespace Ep\Swoole\Tcp;

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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Server as SwooleServer;
use Swoole\WebSocket\Frame;
use Throwable;

class Server implements ServerInterface
{
    use ServerTrait;

    /**
     * {@inheritDoc}
     */
    protected function getServerClass(): string
    {
        return SwooleServer::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function onRequest(): void
    {
        $this->getServer()->on(SwooleEvent::ON_RECEIVE, function (SwooleServer $server, int $fd, int $reactorId, string $data) {
            echo "[#" . $server->worker_id . "]\tClient[$fd]: $data\n";
        });
    }
}
