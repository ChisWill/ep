<?php

declare(strict_types=1);

namespace Ep\Tests\App\Advance\TestDir\BackAdmin\Command;

use Ep\Console\Command;
use Ep\Tests\App\Command\InitCommand;

final class ADGTeCommand extends Command
{
    public function sayAction()
    {
        return $this->success('hi');
    }

    public function callAction()
    {
        $code = $this->getService()->call('init/index', ['name' => 'ChisWill']);

        return $this->success('call over');
    }
}
