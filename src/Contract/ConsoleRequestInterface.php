<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ConsoleRequestInterface
{
    /**
     * 返回格式为：/path/to/command
     */
    public function getRoute(): string;

    /**
     * 参数输入格式为：-d p1=v1 -force p2=v2
     * 返回格式为：
     * 
     * ```
     * [
     *     'd' => true,
     *     'p1' => 'v1',
     *     'force' => true,
     *     'p2' => 'v2',
     * ]
     * ```
     */
    public function getParams(): array;
}
