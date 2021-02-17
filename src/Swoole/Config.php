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
    public string $httpHost = '0.0.0.0';
    public int $httpPort = 9501;
    public bool $httpSSL = false;

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
        throw new InvalidArgumentException("{$name} is invalid.");
    }
}
