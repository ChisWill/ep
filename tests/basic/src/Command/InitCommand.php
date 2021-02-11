<?php

namespace Ep\Tests\Basic\Command;

use Ep\Console\Command;
use Ep\Standard\ConsoleRequestInterface;

class InitCommand extends Command
{
    public function indexAction(ConsoleRequestInterface $request)
    {
        $message = 'Welcome Basic';

        tes($message, $request->getParams());
    }
}
