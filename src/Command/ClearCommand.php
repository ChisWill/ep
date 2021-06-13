<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Base\Config;
use Ep\Console\Command;
use Ep\Contract\ConsoleResponseInterface;
use Ep\Helper\File;
use Yiisoft\Aliases\Aliases;

final class ClearCommand extends Command
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;

        $this->setDefinition('index')->setDescription('Clear runtime cache');
    }

    /**
     * 清除缓存
     */
    public function indexAction(Aliases $aliases): ConsoleResponseInterface
    {
        $runtimeDir = $aliases->get($this->config->runtimeDir);
        File::rmdir($runtimeDir);
        File::mkdir($runtimeDir);

        return $this->success();
    }
}
