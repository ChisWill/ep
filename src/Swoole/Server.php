<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep;
use Ep\Swoole\Contract\ServerInterface;
use Ep\Swoole\Http\Server as HttpServer;
use Ep\Swoole\Tcp\Server as TcpServer;
use Ep\Swoole\WebSocket\Server as WebSocketServer;

class Server
{
    const SERVER_HTTP = 1;
    const SERVER_WEBSOCKET = 2;
    const SERVER_TCP = 3;

    private ServerInterface $mainServer;

    private Config $config;
    private array $settings;

    public function __construct(Config $config, array $settings)
    {
        $this->config = $config;
        $this->settings = $settings + $this->config->settings;
    }

    public function run(): void
    {
        $this->mainServer = $this->createServer($this->config->type, [
            'host' => $this->config->host,
            'port' => $this->config->port,
            'mode' => $this->config->mode,
            'sockType' => $this->config->sockType,
            'settings' => $this->settings
        ]);

        foreach ($this->config->servers as $config) {
            $this->mainServer->listen($config['host'], $config['port'], $config['sock_type'] ?? SWOOLE_SOCK_TCP);
        }

        $this->mainServer->start();
    }

    private function createServer($type, $config): ServerInterface
    {
        switch ($type) {
            case self::SERVER_HTTP:
                return Ep::getInjector()->make(HttpServer::class, [
                    'config' => $config,
                ]);
            case self::SERVER_WEBSOCKET:
                return Ep::getInjector()->make(WebSocketServer::class, [
                    'config' => $config,
                ]);
            default:
                return Ep::getInjector()->make(TcpServer::class, [
                    'config' => $config,
                ]);
        }
    }

    private function start(): void
    {
    }
}
