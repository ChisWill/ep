<?php

declare(strict_types=1);

namespace Ep\Base;

use InvalidArgumentException;

final class Config
{
    /**
     * 项目根命名空间
     */
    public string $appNamespace = 'src';
    /**
     * 项目根目录地址
     */
    public string $basePath = '';
    /**
     * 控制器文件夹名
     */
    public string $controllerDirname = 'controller';
    /**
     * 默认 Controller
     */
    public string $defaultController = 'index';
    /**
     * 默认 Action
     */
    public string $defaultAction = 'index';
    /**
     * 当前语言
     */
    public string $language = 'zh-CN';
    /**
     * 视图文件夹地址
     */
    public string $viewFilePath = '@root/view';
    /**
     * 路由规则
     */
    private array $routeRules = [];
    /**
     * 组件配置
     */
    private array $components = [];
    /**
     * di 配置
     */
    private array $definitions = [];
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

    public function getRouteRules(): array
    {
        return $this->routeRules;
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
