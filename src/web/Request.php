<?php

namespace ep\web;

use ep\base\Request as BaseRequest;

class Request extends BaseRequest
{
    private $_config;

    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    public function createUrl(string $action, $params = [])
    {
        return sprintf('%s/%s%s%s', $this->getHostInfo(), $action, $params ? '?' : '', http_build_query($params));
    }

    private $_queryParams = [];

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
                return str_replace(array_map(fn ($v) => sprintf('<%s>', $v), $keys), $match, $route);
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

    public function getIsSecureConnection()
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    private $_hostInfo;

    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            $secure = $this->getIsSecureConnection();
            $http = $secure ? 'https' : 'http';
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            } elseif (isset($_SERVER['SERVER_NAME'])) {
                $this->_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getPort();
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure)) {
                    $this->_hostInfo .= ':' . $port;
                }
            }
        }

        return $this->_hostInfo;
    }

    private $_port;

    public function getPort()
    {
        if ($this->_port === null) {
            $this->_port = !$this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
        }

        return $this->_port;
    }

    public function getSecurePort()
    {
        if ($this->_securePort === null) {
            $this->_securePort = $this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 443;
        }

        return $this->_securePort;
    }

    public function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }

    public function getQueryParams()
    {
        return $_GET + $this->_queryParams;
    }
}
