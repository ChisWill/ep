<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ConsoleRequestInterface
{
    /**
     * 返回格式：/path/to/command
     */
    public function getRoute(): string;

    /**
     * 参数输入格式：
     * 
     * `-d -force p1=v1 p2=v2`
     * 
     * 返回格式：
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
     * 设置控制台输入参数的访问别名，参数序号从 1 开始
     * 
     * 设置格式：
     * 
     * ```
     * [
     *     'table' => 1
     * ]
     * ```
     * 
     * 设置别名后，输入命令参数：
     * 
     * `./vendor/bin/ep generate/model user`
     * 
     * `$this->getParams()` 返回：
     * 
     * ```
     * [
     *     'table' => 'user'
     * ]
     * ```
     */
    public function setAlias(array $alias): void;
}
