<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

use Ep\Swoole\Config;
use Swoole\Server;

interface ServerInterface
{
    public function init(Config $config): void;

    public function start(array $settings): void;

    public function getServer(): Server;
}
