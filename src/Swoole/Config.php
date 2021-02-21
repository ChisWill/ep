<?php

declare(strict_types=1);

namespace Ep\Swoole;

use InvalidArgumentException;

final class Config
{
    /**
     * 应用配置项
     */
    public array $appConfig = [];
    /**
     * 主服务基本配置项，仅支持异步服务端
     * 
     * @see https://wiki.swoole.com/#/server/methods?id=__construct
     */
    public string $host = '0.0.0.0';
    public int $port = 9501;
    public int $mode = SWOOLE_PROCESS;
    public int $sockType = SWOOLE_SOCK_TCP;
    /**
     * 主服务类型
     */
    public int $type = SwooleServer::HTTP;
    /**
     * 主服务配置
     * 
     * @see https://wiki.swoole.com/#/server/setting
     */
    public array $settings = [];
    /**
     * 主服务事件
     * 
     * @see https://wiki.swoole.com/#/server/events
     */
    public array $events = [];
    /**
     * 子服务配置，除了不需要`$mode`外，其它与主服务配置相同
     */
    public array $servers = [];

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        throw new InvalidArgumentException("The \"{$name}\" configuration is invalid.");
    }
}
