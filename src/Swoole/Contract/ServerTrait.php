<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Ep\Swoole\Config;
use Ep\Swoole\SwooleEvent;
use Swoole\Server;
use Swoole\Server\Port;
use InvalidArgumentException;

trait ServerTrait
{
    private Server $server;
    /** 
     * @var Port[] $ports 
     */
    private array $ports;

    public function init(Config $config): void
    {
        $class = $this->getServerClass();
        $this->server = new $class(
            $config->host,
            $config->port,
            $config->mode,
            $config->sockType
        );

        $this->onEvents($this->server, $config->events);

        $this->listenServers($this->server, $config->servers);
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function start(array $settings): void
    {
        $this->onRequest();

        $this->server->set($settings);

        $this->server->start();
    }

    private function listenServers(Server $server, array $servers): void
    {
        foreach ($servers as $config) {
            if (!isset($config['port'])) {
                throw new InvalidArgumentException("The \"servers[port]\" configuration is required.");
            }
            $config['host'] ??= '0.0.0.0';
            $port = $server->listen(
                $config['host'],
                $config['port'],
                $config['socketType'] ?? SWOOLE_SOCK_TCP,
            );
            if ($port instanceof Port) {
                $port->set($config['settings'] ?? []);
                $this->onEvents($port, $config['events'] ?? []);
                $this->ports[] = $port;
            } else {
                throw new InvalidArgumentException("Failed to listen server port [{$config['host']}:{$config['port']}]");
            }
        }
    }

    /**
     * @param Server|Port $port
     */
    private function onEvents($port, array $events): void
    {
        foreach ($events as $event => $callback) {
            if (!SwooleEvent::isSwooleEvent($event)) {
                throw new InvalidArgumentException("The \"servers[events]\" configuration must have Swoole Event as the key of the array.");
            }
            if (!is_callable($callback)) {
                throw new InvalidArgumentException("The \"servers[events]\" configuration is an array of string-callback pairs.");
            }
            $port->on($event, $callback);
        }
    }

    abstract protected function getServerClass(): string;

    abstract protected function onRequest(): void;
}
