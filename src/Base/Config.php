<?php

declare(strict_types=1);

namespace Ep\Base;

use Yiisoft\Http\Method;
use Closure;
use InvalidArgumentException;

final class Config
{
    /**
     * 项目根命名空间
     */
    public string $appNamespace = 'App';
    /**
     * Action 后缀
     */
    public string $actionSuffix = 'Action';
    /**
     * 项目根目录地址
     */
    public string $basePath = '';
    /**
     * 项目根 URL，必须以 / 开头
     */
    public string $baseUrl = '/';
    /**
     * 控制器所在文件夹名以及类名后缀，强制统一
     */
    public string $controllerDirAndSuffix = 'Controller';
    /**
     * 默认 Controller
     */
    public string $defaultController = 'index';
    /**
     * 默认 Action
     */
    public string $defaultAction = 'index';
    /**
     * Mysql dsn
     */
    public string $mysqlDsn = '';
    /**
     * Mysql 用户
     */
    public string $mysqlUsername = '';
    /**
     * Mysql 密码
     */
    public string $mysqlPassword = '';
    /**
     * 默认路由规则
     */
    public array $defaultRoute = [[Method::GET, Method::POST], '{prefix:[\w/]*?}{controller:/?[a-zA-Z]\w*|}{action:/?[a-zA-Z]\w*|}', '<prefix>/<controller>/<action>'];
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
     * Redis Host
     */
    public string $redisHost = 'localhost';
    /**
     * Redis Port
     */
    public int $rediPort = 6379;
    /**
     * Redis Database
     */
    public int $redisDatabase = 0;
    /**
     * Redis Password
     */
    public ?string $redisPassword = null;
    /**
     * 运行时缓存目录地址
     */
    public string $runtimeDir = '@root/runtime';
    /**
     * 秘钥
     */
    public string $secretKey = '';
    /**
     * 视图文件夹地址，支持从路由中获取参数，获取不到时将自动忽略
     */
    public string $viewPath = '@root/views/<prefix>';
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
     * function (RouteCollector $route) {
     *     $route->addGroup('/api', function (RouteCollector $route) {
     *         $route->get('/error/index', 'error/index');
     *     });
     * };
     * ```
     */
    private Closure $route;
    /**
     * 事件配置
     */
    private array $events = [];
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
        if ($this->secretKey === '') {
            throw new InvalidArgumentException('The "secretKey" configuration is required.');
        }
    }

    public function __set($name, $value)
    {
        throw new InvalidArgumentException("{$name} is invalid.");
    }

    public function getRoute(): Closure
    {
        return $this->route;
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
