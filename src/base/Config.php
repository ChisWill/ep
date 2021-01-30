<?php

declare(strict_types=1);

namespace Ep\Base;

use Closure;
use InvalidArgumentException;

final class Config
{
    /**
     * 项目根命名空间
     */
    public string $appNamespace = 'Src';
    /**
     * 项目根目录地址
     */
    public string $basePath = '';
    /**
     * 项目根 URL，必须以 / 开头
     */
    public string $baseUrl = '/';
    /**
     * 控制器所在文件夹名
     */
    public string $controllerDirname = 'Controller';
    /**
     * 默认 Controller
     */
    public string $defaultController = 'index';
    /**
     * 默认 Action
     */
    public string $defaultAction = 'index';
    /**
     * 是否开启调试模式
     */
    public bool $debug = false;
    /**
     * 项目运行的当前环境
     */
    public string $env = 'prod';
    /**
     * 默认的错误处理器
     */
    public string $errorHandler = 'error/index';
    /**
     * 当前语言
     */
    public string $language = 'zh-CN';
    /**
     * 视图文件夹地址
     */
    public string $viewFilePath = '@root/View';
    /**
     * 组件配置
     */
    private array $components = [];
    /**
     * di 配置
     */
    private array $definitions = [];
    /**
     * 以匿名函数方式设置路由规则，具体方式参看示例
     * 
     * For example:
     * ```php
     * 
     * use FastRoute\RouteCollector;
     *
     * $config->router = function (RouteCollector $route) {
     *     $route->addGroup('/api', function (RouteCollector $r) {
     *         $r->get('/error/index', 'error/index');
     *     });
     * };
     * ```
     */
    private Closure $router;
    /**
     * 常规配置项
     */
    private array $params = [];

    public function __construct($config = [])
    {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
        if ($this->basePath === '') {
            throw new InvalidArgumentException('The "basePath" configuration is required.');
        }
    }

    public function __set($name, $value)
    {
        throw new InvalidArgumentException("{$name} is invalid.");
    }

    public function getRouter(): Closure
    {
        return $this->router;
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
