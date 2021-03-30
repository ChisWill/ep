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
     * 标准输入格式为：
     * 
     * `-d -force p1=v1 p2=v2`
     * 
     * 返回格式为：
     * 
     * ```
     * [
     *     'd' => true,
     *     'force' => true,
     *     'p1' => 'v1',
     *     'p2' => 'v2',
     * ]
     * ```
     */
    public function getParams(): array;

    /**
     * 设置控制台输入参数的访问别名，参数序号从 1 开始；另外，需要确保设置后，`getParams()` 能正确响应该设置
     * 输入格式为：
     * 
     * ```
     * [
     *     'table' => 1,
     *     'name' => 2
     * ]
     * ```
     */
    public function setAlias(array $alias): void;
}
