<?php

declare(strict_types=1);

namespace Ep\Swoole\Contract;

interface ServerInterface
{
    public function start(): void;

    public function listen(string $host, int $port, int $socketType): ServerInterface;
}
