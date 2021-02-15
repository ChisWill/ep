<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Contract\ConsoleRequestInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Application
{
    public function __construct(array $config)
    {
        Ep::init($config);
    }

    public function run(): void
    {
        $request = $this->createRequest();

        $this->register($request);

        $this->handleRequest($request);
    }

    /**
     * 创建请求对象
     * 
     * @return ServerRequestInterface|ConsoleRequestInterface
     */
    abstract protected function createRequest();

    /**
     * 注册事件
     * 
     * @param ServerRequestInterface|ConsoleRequestInterface $request
     */
    abstract protected function register($request): void;

    /**
     * 处理请求
     * 
     * @param ServerRequestInterface|ConsoleRequestInterface $request
     */
    abstract protected function handleRequest($request): void;
}
