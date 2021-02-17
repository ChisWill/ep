<?php

declare(strict_types=1);

namespace Ep\Swoole;

use Ep;
use Ep\Console\Application as Console;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Swoole\Http\Application as HttpApplication;

final class Server
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

        $this->handlerRequest($request);
    }

    private function handlerRequest(ConsoleRequestInterface $request)
    {
        $route = ltrim($request->getRoute(), '/');
        $params = $request->getParams();

        switch ($route) {
            default:
                $application = Ep::getInjector()->make(HttpApplication::class, ['config' => $this->config]);
                $application->run();
                break;
        }
    }
}
