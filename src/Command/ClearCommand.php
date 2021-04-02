<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Base\Config;
use Ep\Console\Command;
use Ep\Helper\File;
use Yiisoft\Aliases\Aliases;
use RuntimeException;

final class ClearCommand extends Command
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function indexAction(Aliases $aliases): string
    {
        try {
            $runtimeDir = $aliases->get($this->config->runtimeDir);
            File::rmdir($runtimeDir);
            File::mkdir($runtimeDir);
            return '';
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }
    }
}
