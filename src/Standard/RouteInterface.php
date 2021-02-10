<?php

declare(strict_types=1);

namespace Ep\Standard;

interface RouteInterface
{
    /**
     * 匹配请求参数，返回路由信息
     */
    public function matchRequest(string $path, string $method = 'GET'): array;
    /**
     * 处理路由信息，返回处理器与未捕获到的路由参数
     */
    public function solveRouteInfo(array $routeInfo): array;
    /**
     * 解析处理器，返回 Controller 与 Action
     * 
     * @return string[]
     */
    public function parseHandler($handler): array;
    /**
     * 创建控制器
     */
    public function createController(string $class): ControllerInterface;
    /**
     * 返回捕获到的路由参数
     */
    public function getCaptureParams(): array;
}
