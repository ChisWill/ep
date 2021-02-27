<?php

declare(strict_types=1);

namespace Ep\Tests\App\Command;

use Ep;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;

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

        return 'ok';
    }
}
