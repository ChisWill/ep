<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Helper\Alias;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use RuntimeException;

use function FastRoute\cachedDispatcher;

class Router
{
    private string $path;
    private string $method;
    private Config $config;

    public function __construct(string $path, string $method = 'GET')
    {
        $this->path = $path;
        $this->method = $method;
        $this->config = Ep::getConfig();
    }

    public function match(): array
    {
        $dispatcher = cachedDispatcher(function (RouteCollector $route) {
            $callback = $this->config->getRouter();
            if (is_callable($callback)) {
                call_user_func($callback, $route);
            }
            $route->addRoute(['GET', 'POST'], '/{__ctrl:[a-zA-Z]\w*}/{__action:[a-zA-Z]\w*}', 'defaultRule');
        }, [
            'cacheFile' => Alias::get('@root/runtime/routeRules.cache'),
            'cacheDisabled' => $this->config->env !== 'prod'
        ]);
        return $dispatcher->dispatch($this->method, $this->path);
    }

    public function solveRouteInfo($routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $params = [];
                $handler = Ep::getConfig()->errorHandler;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $params = [];
                $handler = Ep::getConfig()->errorHandler;
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $params = $routeInfo[2];
                break;
        }
        return [$handler, $params];
    }

    public function createController($handler, $params)
    {
        if ($handler === 'defaultRule') {
            ['__ctrl' => $ctrl, '__action' => $action] = $params;
            if (!class_exists($controllerName)) {
                throw new RuntimeException("{$controllerName} is not found.");
            }
            $controller = new $controllerName;
            if (!method_exists($controller, $actionName)) {
                throw new RuntimeException("{$actionName} is not found.");
            }
            return call_user_func([$controller, $actionName], $request);
        } else {
            if (is_callable($handler)) {
            } else {
                throw new RuntimeException('Page is not found.');
            }
        }
    }

    public function getControllerActionName(string $path): array
    {
        $config = Ep::getConfig();
        $pieces = explode('/', ltrim($path, '/'));
        $prefix = '';
        switch (count($pieces)) {
            case 0:
                $controllerName = $config->defaultController;
                $actionName = $config->defaultAction;
                break;
            case 1:
                $controllerName = $pieces[0];
                $actionName = $config->defaultAction;
                break;
            default:
                $actionName = array_pop($pieces);
                $controllerName = array_pop($pieces);
                $prefix = implode('\\', $pieces) . '\\';
                break;
        }
        $controllerName = sprintf('%s\\%s%s\\%sController', $config->appNamespace, $prefix, $config->controllerDirname, ucfirst($controllerName));
        return [$controllerName, $actionName];
    }
}
