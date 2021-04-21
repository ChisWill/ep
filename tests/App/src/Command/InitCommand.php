<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Psr\Log\LoggerInterface;

class InitCommand extends Command
{
    protected function alias(): array
    {
        return [
            'table' => 1,
            'field' => 2
        ];
    }

    public function indexAction()
    {
        $message = 'Welcome Basic';

        return $message;
    }

    public function logAction(LoggerInterface $logger)
    {
        $logger->info('log info', ['act' => self::class]);

        return 'ok';
    }

    public function requestAction(ConsoleRequestInterface $request)
    {
        return [
            'route' => $request->getRoute(),
            'params' => $request->getParams()
        ];
    }
}
