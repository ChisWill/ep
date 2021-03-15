<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Web\Middleware\InterceptorMiddleware;
use Ep\Web\Middleware\RouteMiddleware;
use Yiisoft\Http\Method;
use Yiisoft\Session\SessionMiddleware;
use Closure;
use InvalidArgumentException;

final class Config
{
    /**
     * 项目根命名空间
     */
    public string $appNamespace = 'App';
    /**
     * 项目根目录地址，必填
     */
    public string $rootPath = '';
    /**
     * 默认路由的根 URL
     */
    public string $baseUrl = '';
    /**
     * 是否开启调试模式
     */
    public bool $debug = true;
    /**
     * 项目运行的当前环境
     */
    public string $env = 'prod';
    /**
     * 模块类名
     */
    public string $moduleName = 'Module';
    /**
     * Web 控制器所在目录名以及类名后缀，强制统一
     */
    public string $controllerDirAndSuffix = 'Controller';
    /**
     * Console 控制器所在目录名以及类名后缀，强制统一
     */
    public string $commandDirAndSuffix = 'Command';
    /**
     * Action 后缀
     */
    public string $actionSuffix = 'Action';
    /**
     * 默认 Controller
     */
    public string $defaultController = 'index';
    /**
     * 默认 Action
     */
    public string $defaultAction = 'index';
    /**
     * 运行时缓存目录路径
     */
    public string $runtimeDir = '@root/runtime';
    /**
     * Vendor 目录路径
     */
    public string $vendorPath = '@root/vendor';
    /**
     * View 目录路径
     */
    public string $viewPath = '@root/views';
    /**
     * Layout 目录名称
     */
    public string $layoutDir = '_layouts';
    /**
     * 默认路由规则
     */
    public array $defaultRoute = [[Method::GET, Method::POST], '{prefix:[\w/]*?}{controller:/?[a-zA-Z][\w-]*|}{action:/?[a-zA-Z]\w*|}', '<prefix>/<controller>/<action>'];
    /**
     * Web 中间件
     */
    public array $webMiddlewares = [
        RouteMiddleware::class,
        SessionMiddleware::class,
        InterceptorMiddleware::class
    ];
    /**
     * 事件配置
     */
    public array $events = [];
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
     * 项目基础秘钥，必填
     */
    public string $secretKey = '';
    /**
     * 当前语言
     */
    public string $language = 'zh-CN';
    /**
     * di 配置
     */
    public array $definitions = [];
    /**
     * 常规配置项
     */
    public array $params = [];
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
    private ?Closure $route = null;

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
        if ($this->rootPath === '') {
            throw new InvalidArgumentException('The "rootPath" configuration is required.');
        }
        if ($this->secretKey === '') {
            throw new InvalidArgumentException('The "secretKey" configuration is required.');
        }
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        throw new InvalidArgumentException("The \"{$name}\" configuration is invalid.");
    }

    public function getRouteRule(): Closure
    {
        return $this->route ?: fn () => true;
    }
}
