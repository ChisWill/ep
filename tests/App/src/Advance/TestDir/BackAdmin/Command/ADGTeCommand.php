<?php

declare(strict_types=1);

namespace Ep\Tests\App\Advance\TestDir\BackAdmin\Command;

use Ep\Console\Command;

final class ADGTeCommand extends Command
{
    public function sayAction()
    {
        return $this->success('hi');
    }
}
