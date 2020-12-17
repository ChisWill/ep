<?php

namespace ep\web;

use ep\base\Request as BaseRequest;

class Request extends BaseRequest
{
    private $_config;
    private $_queryParams = [];

    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    public function getQueryParams()
    {
        return $_GET + $this->_queryParams;
    }

    public function solveRouteRules(array $rules, string $requestPath)
    {
        foreach ($rules as $rule => $route) {
            $keys = [];
            $pattern = preg_replace_callback(
                '/\<(.+)\:(.+)\>/U',
                function ($match) use (&$keys) {
                    $keys[] = $match[1];
                    return sprintf('(%s)', $match[2]);
                },
                $rule
            );
            $r = preg_match(sprintf('#^/%s$#', $pattern), $requestPath, $match);
            if ($r) {
                unset($match[0]);
                $this->_queryParams = array_combine($keys, $match);
                $route = str_replace(array_map(function ($v) {
                    return sprintf('<%s>', $v);
                }, $keys), $match, $route);
                return $route;
            }
        }
        return $requestPath;
    }

    public function solvePath(string $path)
    {
        $pieces = explode('/', ltrim($path, '/'));
        $actionName = array_pop($pieces);
        $controllerName = implode('\\', $pieces);
        if (!$actionName) {
            $actionName = $this->_config->defaultAction;
        }
        if (!$controllerName) {
            $controllerName = $this->_config->defaultController;
        }
        $controllerName = $this->_config->controllerNamespace . '\\' . $controllerName . 'Controller';
        return [$controllerName, $actionName];
    }

    public function getRequestPath(): string
    {
        $queryString = $this->getQueryString();
        if ($queryString) {
            $queryString = '?' . $queryString;
        }
        return str_replace($queryString, '', $this->getRequestUri());
    }

    public function getRequestUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    public function getQueryString(): string
    {
        return $_SERVER['QUERY_STRING'] ?? '';
    }

    public function getMethod(): string
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }

        return 'GET';
    }
}
