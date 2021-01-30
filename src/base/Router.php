<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Helper\Alias;
use Ep\Helper\Arr;
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
            $route->addGroup($this->config->baseUrl, function (RouteCollector $r) {
            $r->addRoute(['GET', 'POST'], '{controller:/?[a-zA-Z]\w*|}{action:/?[a-zA-Z]\w*|}[/]', '<controller>/<action>');
            });
        }, [
            'cacheFile' => Alias::get('@root/runtime/routeRules.cache'),
            'cacheDisabled' => $this->config->env !== 'prod'
        ]);
        return $this->solveRouteInfo($dispatcher->dispatch($this->method, $this->path));
    }

    private function solveRouteInfo(array $routeInfo)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $params = [];
                $handler = $this->config->errorHandler;
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $params = [];
                $handler = $this->config->errorHandler;
                break;
            case Dispatcher::FOUND:
                $params = $routeInfo[2];
                test($routeInfo);
                if ($routeInfo[1] === 'defaultRule') {
                    unset($params['slash']);
                    $ctrl = Arr::remove($params, 'controller') ?: $this->config->defaultController;
                    $action = Arr::remove($params, 'action') ?: '/' . $this->config->defaultAction;
                    $handler = $ctrl . $action;
                }
                break;
        }
        return [$handler, $params];
    }

    public function createController($handler, $params)
    {
        if ($handler === 'defaultRule') {
            ['controller' => $ctrl, 'action' => $action] = $params;
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
