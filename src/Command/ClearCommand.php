<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep;
use Ep\Base\Config;
use Ep\Console\Command;
use Ep\Console\ConsoleRequest;
use Ep\Helper\Alias;
use Ep\Helper\File;
use RuntimeException;

final class ClearCommand extends Command
{
    private Config $config;

    public function __construct()
    {
        $this->config = Ep::getConfig();
    }

    public function indexAction(ConsoleRequest $request): string
    {
        try {
            $runtimeDir = Alias::get($this->config->runtimeDir);
            File::rmdir($runtimeDir);
            File::mkdir($runtimeDir);
            return '';
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }
    }
}
