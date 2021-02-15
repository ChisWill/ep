<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ConsoleRequestInterface
{
    /**
     * 路由格式为：/path/to/command
     */
    public function getRoute(): string;

    /**
     * 参数格式为：param1=foo param2=bar -d -force
     */
    public function getParams(): array;
}
