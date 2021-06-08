<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep\Console\Command;
use Ep\Console\Service;
use Ep\Contract\ConsoleRequestInterface;
use Psr\Log\LoggerInterface;

class InitCommand extends Command
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function indexAction()
    {
        $message = 'Welcome Basic';

        return $this->success($message);
    }

    public function logAction(LoggerInterface $logger)
    {
        $logger->info('log info', ['act' => self::class]);

        return $this->success();
    }

    public function requestAction(ConsoleRequestInterface $request)
    {
        t([
            'route' => $request->getRoute(),
            'options' => $request->getOptions(),
            'argvs' => $request->getArguments()
        ]);

        return $this->success();
    }
}
