<?php

namespace Ep\Tests\Advance\Console\Command;

use Ep\Console\Command;
use Ep\Standard\ConsoleRequestInterface;

class InitCommand extends Command
{
    public function indexAction(ConsoleRequestInterface $request)
    {
        $message = 'Welcome Advance';

        tes($message, $request->getParams());
    }

    public function viewAction()
    {
        $view = $this->getView()->render('view');
        echo $view;
    }
}
