<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;

class Router
{
    private array $rules;
    private array $ruleParams = [];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function match(string $path): string
    {
        foreach ($this->rules as $rule => $route) {
            $keys = [];
            $pattern = preg_replace_callback(
                '/\<(.+)\:(.+)\>/U',
                function ($match) use (&$keys) {
                    $keys[] = $match[1];
                    return sprintf('(%s)', $match[2]);
                },
                $rule
            );
            $hit = preg_match(sprintf('#^/%s$#', $pattern), $path, $match);
            if ($hit) {
                unset($match[0]);
                $this->ruleParams = array_combine($keys, $match);
                return str_replace(array_map(fn ($v) => sprintf('<%s>', $v), $keys), $match, $route);
            }
        }
        return $path;
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

    public function tmp()
    {
        $request = Ep::getDi()->get('request');
        $requestPath = $request->getRequestPath();
        if (count($config->routeRules) > 0) {
            $requestPath = $request->solveRouteRules($config->routeRules, $requestPath);
        }
        [$controllerName, $actionName] = $request->solvePath($requestPath);
        if (!class_exists($controllerName)) {
            throw new RuntimeException("{$controllerName} is no");
        }
        /** @var Controller $controller */
        $controller = new $controllerName;
        if (!method_exists($controller, $actionName)) {
            throw new RuntimeException(Exception::NOT_FOUND_ACTION);
        }
        if (in_array($actionName, ['beforeAction', 'afterAction'])) {
            throw new Exception(Exception::NOT_FOUND_ACTION);
        }
        if ($controller->beforeAction()) {
            $response = $controller->$actionName($request, Ep::getDi()->get('response'));
            $controller->afterAction($response);
            return $response;
        } else {
            return null;
        }
        return Ep::getDi()->get(ResponseFactoryInterface::class)->createResponse();
    }
}
