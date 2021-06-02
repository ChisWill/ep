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

    public function before($request)
    {
        return true;
    }

    /**
     * 清除缓存
     */
    public function indexAction(Aliases $aliases): int
    {
        try {
            $runtimeDir = $aliases->get($this->config->runtimeDir);
            File::rmdir($runtimeDir);
            File::mkdir($runtimeDir);
            return $this->success();
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage());
        }
    }

    public function after($request, $response)
    {
        return $response;
    }
}
