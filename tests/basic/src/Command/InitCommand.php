<?php

namespace Ep\Tests\Basic\Command;

use Ep;
use Ep\Console\Command;
use Ep\Standard\ConsoleRequestInterface;

class InitCommand extends Command
{
    public function indexAction(ConsoleRequestInterface $request)
    {
        $message = 'Welcome Basic';

        tes($message, $request->getParams());
    }

    public function logAction()
    {
        Ep::getLogger()->info('log info', ['act' => self::class]);
    }
}
