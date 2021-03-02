<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;

class InitCommand extends Command
{
    protected function alias(): array
    {
        return [
            'table' => 1,
            'field' => 3
        ];
    }

    public function indexAction()
    {
        $message = 'Welcome Basic';

        return $message;
    }

    public function logAction()
    {
        Ep::getLogger()->info('log info', ['act' => self::class]);

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
