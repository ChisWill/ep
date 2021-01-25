<?php

namespace Ep\web;

use Ep\base\Request as BaseRequest;
use Ep\helper\Ep;

class Request extends BaseRequest
{
    private Config $_config;

    public function __construct()
    {
        $this->_config = Ep::getConfig();
    }

    public function createUrl(string $action, $params = []): string
    {
        return sprintf('%s/%s%s%s', $this->getHostInfo(), $action, $params ? '?' : '', http_build_query($params));
    }

    private array $_ruleParams = [];

    public function solveRouteRules(array $rules, string $requestPath): string
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
                $this->_ruleParams = array_combine($keys, $match);
                return str_replace(array_map(fn ($v) => sprintf('<%s>', $v), $keys), $match, $route);
            }
        }
        return $requestPath;
    }

    public function solvePath(string $path): array
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
        $controllerName = Ep::createControllerName($this->_config->controllerNamespace, $controllerName);
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

    public function isSecureConnection(): bool
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    private $_hostInfo;

    public function getHostInfo(): string
    {
        if ($this->_hostInfo === null) {
            $secure = $this->isSecureConnection();
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

    public function getPort(): int
    {
        if ($this->_port === null) {
            $this->_port = !$this->isSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
        }

        return $this->_port;
    }

    public function getSecurePort(): int
    {
        if ($this->_securePort === null) {
            $this->_securePort = $this->isSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 443;
        }

        return $this->_securePort;
    }

    public function getHost(): string
    {
        return $_SERVER['HTTP_HOST'];
    }

    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function getRawBody(): string
    {
        return file_get_contents('php://input');
    }

    private $_queryParams;

    public function getQueryParams(): array
    {
        if ($this->_queryParams === null) {
            $this->_queryParams = $_GET + $this->_ruleParams;
        }
        return $this->_queryParams;
    }

    public function getQueryParam(string $name, $defaultValue = null)
    {
        return $this->getQueryParams()[$name] ?? $defaultValue;
    }

    private $_bodyParams;

    public function getBodyParams()
    {
        if ($this->_bodyParams === null) {
            $this->_bodyParams = $_POST;
        }
        return $this->_bodyParams;
    }

    public function getBodyParam(string $name, $defaultValue = null)
    {
        return $this->getBodyParams()[$name] ?? $defaultValue;
    }

    public function get($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->getQueryParams();
        } else {
            return $this->getQueryParam($name, $defaultValue);
        }
    }

    public function post($name = null, $defaultValue = null)
    {
        if ($name === null) {
            return $this->getBodyParams();
        } else {
            return $this->getBodyParam($name, $defaultValue);
        }
    }
}
