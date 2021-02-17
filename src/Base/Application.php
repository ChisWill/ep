<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;

abstract class Application
{
    public function __construct(?array $config = null)
    {
        if ($config !== null) {
            Ep::init($config);
        }
    }

    public function run(): void
    {
        $request = $this->createRequest();

        $this->register($request);

        $this->send($request, $this->handleRequest($request));
    }

    /**
     * 创建请求对象
     * 
     * @return mixed
     */
    abstract public function createRequest();

    /**
     * 注册事件
     * 
     * @param mixed $request
     */
    abstract public function register($request): void;

    /**
     * 处理请求
     * 
     * @param  mixed $request
     * 
     * @return mixed
     */
    abstract public function handleRequest($request);

    /**
     * 发送结果
     * 
     * @param mixed $request
     * @param mixed $response
     */
    abstract public function send($request, $response): void;
}
