<?php

declare(strict_types=1);

namespace Ep\Base;

use Closure;
use InvalidArgumentException;

/**
 * Do not set the property after instantiation
 */
final class Config
{
    /**
     * Application root namespace
     */
    public string $rootNamespace = 'App';
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
     * Web controller suffix
     */
    public string $controllerSuffix = 'Controller';
    /**
     * Console command suffix
     */
    public string $commandSuffix = 'Command';
    /**
     * Configure annotation handlers
     */
    public array $configureHandlers = [];
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
     * The algorithm cipher
     */
    public string $algoCipher = 'AES-128-CBC';
    /**
     * Params
     */
    public array $params = [];
    /**
     * ```php
     * 
     * use Ep\Base\Config;
     * 
     * return static fn (Ep\Base\Config $config, array $params): array => [
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
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        throw new InvalidArgumentException("The \"{$name}\" configuration is invalid.");
    }

    /**
     * Call before the application runs
     */
    public function switch(array $properties): array
    {
        $result = [];
        foreach ($properties as $key => $value) {
            $result[$key] = $this->$key;
            $this->$key = $value;
        }
        return $result;
    }

    public function getDi(): array
    {
        return $this->di ? call_user_func($this->di, $this, $this->params) : [];
    }

    public function getRouteRule(): Closure
    {
        return $this->route ?? static fn (): bool => true;
    }

    public function isEp(string $rootNamespace = null): bool
    {
        return ($rootNamespace ?? $this->rootNamespace) === 'Ep';
    }
}
