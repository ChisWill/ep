<?php

declare(strict_types=1);

namespace Ep\Base;

use Closure;
use InvalidArgumentException;

final class Config
{
    /**
     * Application root namespace
     */
    public string $rootNamespace = 'App';
    /**
     * Application root path
     */
    public string $rootPath = '';
    /**
     * Path aliases
     */
    public array $aliases = [];
    /**
     * Default base url
     */
    public string $baseUrl = '';
    /**
     * Is debug
     */
    public bool $debug = true;
    /**
     * Current environment
     */
    public string $env = 'prod';
    /**
     * Module class
     */
    public string $moduleName = 'Module';
    /**
     * Web controller directory and suffix
     */
    public string $controllerDirAndSuffix = 'Controller';
    /**
     * Console controller directory and suffix
     */
    public string $commandDirAndSuffix = 'Command';
    /**
     * Database migration table name
     */
    public string $migrationTableName = 'migration';
    /**
     * Action suffix
     */
    public string $actionSuffix = 'Action';
    /**
     * Default Controller
     */
    public string $defaultController = 'index';
    /**
     * Default action
     */
    public string $defaultAction = 'index';
    /**
     * Runtime directory
     */
    public string $runtimeDir = '@root/runtime';
    /**
     * Vendor directory
     */
    public string $vendorPath = '@root/vendor';
    /**
     * View directory
     */
    public string $viewPath = '@root/views';
    /**
     * Layout directory
     */
    public string $layoutDir = '_layouts';
    /**
     * Events
     */
    public array $events = [];
    /**
     * Application secretKey
     */
    public string $secretKey = '';
    /**
     * Params
     */
    public array $params = [];
    /**
     * ```php
     * 
     * use Ep\Base\Config;
     * 
     * return static fn (Config $config): array => [
     *     FooInterface::class => Foo::class
     * ];
     * 
     * ```
     */
    private ?Closure $di = null;
    /**
     * ```php
     * 
     * use FastRoute\RouteCollector;
     *
     * return function (RouteCollector $route): void {
     *     $route->addGroup('/api', function (RouteCollector $route) {
     *         $route->get('/error/index', 'error/index');
     *     });
     * };
     * 
     * ```
     */
    private ?Closure $route = null;

    public function __construct(array $config)
    {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
        if ($this->debug) {
            if ($this->rootPath === '') {
                throw new InvalidArgumentException('The "rootPath" configuration is required.');
            }
            if ($this->secretKey === '') {
                throw new InvalidArgumentException('The "secretKey" configuration is required.');
            }
        }
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        throw new InvalidArgumentException("The \"{$name}\" configuration is invalid.");
    }

    public function getDi(): array
    {
        return $this->di ? call_user_func($this->di, $this) : [];
    }

    public function getRouteRule(): Closure
    {
        return $this->route ?: static fn (): int => 1;
    }
}
