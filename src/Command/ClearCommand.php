<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep;
use Ep\Console\Command;
use Ep\Console\ConsoleRequest;
use Ep\Helper\Alias;
use Ep\Helper\File;
use Throwable;

final class ClearCommand extends Command
{
    public function indexAction(ConsoleRequest $request): string
    {
        try {
            $runtimeDir = Alias::get(Ep::getConfig()->runtimeDir);
            File::rmdir($runtimeDir);
            File::mkdir($runtimeDir);
            return '';
        } catch (Throwable $t) {
            return $t->getMessage();
        }
    }
}
