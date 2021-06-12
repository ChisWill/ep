<?php

declare(strict_types=1);

namespace Ep\Tests\App\Advance\TestDir\BackAdmin\Command;

use Ep\Console\Module as ConsoleModule;
use Ep\Contract\ConsoleRequestInterface;

final class Module extends ConsoleModule
{
    public function before(ConsoleRequestInterface $request)
    {
        return true;
    }

    public function after(ConsoleRequestInterface $request, int $response): int
    {
        return $response;
    }
}
