<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep\Console\Application as Console;
use Ep\Contract\ConsoleRequestInterface;

final class Application
{
    private Config $config;

    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    public function run(): void
    {
        $console = new Console($this->config->appConfig);

        $request = $console->createRequest();

        $console->register($request);

        $this->handlerRequest($request);
    }

    private function handlerRequest(ConsoleRequestInterface $request)
    {
        $command = $this->parseRoute($request->getRoute());
        $settings = $this->parseParams($request->getParams());
        switch ($command) {
            case '':
            case 'start':
                $server = new SwooleServer($this->config, $settings);
                $server->run();
                break;
            case 'stop':
                break;
            case 'reload':
                break;
            default:
                echo <<<HELP
Usage: php yourfile <command> [mode]
Commands: start, stop, reload
Modes: -d
HELP;
                exit(1);
        }
    }

    private function parseRoute(string $route)
    {
        return trim($route, '/');
    }

    private function parseParams(array $params)
    {
        $settings['daemonize'] = ($params['d'] ?? false) === true;
        return $settings;
    }
}
