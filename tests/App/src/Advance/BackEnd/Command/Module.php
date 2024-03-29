<?php

declare(strict_types=1);

namespace Ep\Tests\App\Advance\BackEnd\Command;

use Ep\Console\Module as ConsoleModule;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;

final class Module extends ConsoleModule
{
    public function before(ConsoleRequestInterface $request, ConsoleResponseInterface $response)
    {
        $this->getService()->writeln('Advance module before');
        return true;
    }

    public function after(ConsoleRequestInterface $request, ConsoleResponseInterface $response): ConsoleResponseInterface
    {
        $this->getService()->writeln('Advance module after');
        return $response;
    }
}
