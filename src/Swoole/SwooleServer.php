<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Http\Server as HttpServer;
use Ep\Swoole\Tcp\Server as TcpServer;
use Ep\Swoole\WebSocket\Server as WebSocketServer;
use Swoole\Runtime;
use InvalidArgumentException;

final class SwooleServer
{
    const HTTP = 1;
    const WEBSOCKET = 2;
    const TCP = 3;

    private Config $config;
    private array $settings;

    public function __construct(Config $config, array $settings)
    {
        $this->config = $config;
        $this->settings = $settings + $this->config->settings;
    }

    public function run(): void
    {
        Runtime::enableCoroutine(true, SWOOLE_HOOK_ALL);

        $mainServer = $this->createMainServer();

        $mainServer->init($this->config);

        $mainServer->start($this->settings);
    }

    private function createMainServer(): ServerInterface
    {
        switch ($this->config->type) {
            case self::HTTP:
                return Ep::getInjector()->make(HttpServer::class);
            case self::WEBSOCKET:
                return Ep::getInjector()->make(WebSocketServer::class);
            case self::TCP:
                return Ep::getInjector()->make(TcpServer::class);
            default:
                throw new InvalidArgumentException('The "type" configuration is invalid.');
        }
    }
}
