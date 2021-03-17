<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Contract\ConfigurableInterface;
use Ep\Contract\ConfigurableTrait;
use Ep\Contract\NotFoundException;
use Ep\Helper\Alias;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Closure;

use function FastRoute\cachedDispatcher;

final class Route implements ConfigurableInterface
{
    use ConfigurableTrait;

    public bool $defaultRoute = true;

    private Config $config;
    private Closure $rule;
    private string $baseUrl;

    public function __construct(Closure $rule, string $baseUrl = '')
    {
        $this->config = Ep::getConfig();
        $this->rule = $rule;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @throws NotFoundException
     */
    public function match(string $path, string $method = 'GET'): array
    {
        return $this->solveRouteInfo(
            cachedDispatcher(function (RouteCollector $route) {
                $route->addGroup($this->baseUrl, $this->rule);
                if ($this->defaultRoute) {
                    $route->addGroup($this->baseUrl, fn (RouteCollector $r) => $r->addRoute(...$this->config->defaultRoute));
                }
            }, [
                'cacheFile' => Alias::get($this->config->runtimeDir . '/route.cache'),
                'cacheDisabled' => $this->config->debug
            ])
                ->dispatch($method, rtrim($path, '/') ?: '/')
        );
    }

    private function solveRouteInfo(array $routeInfo): array
    {
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                return $this->replaceHandler($routeInfo[1], $routeInfo[2]);
            case Dispatcher::METHOD_NOT_ALLOWED:
                return [false, null, null];
            default:
                throw new NotFoundException('Page is not found.');
        }
    }

    /**
     * @param string|array $handler
     */
    private function replaceHandler($handler, array $params): array
    {
        if (is_string($handler)) {
            preg_match_all('/<(\w+)>/', $handler, $matches);
            $match = array_flip($matches[1]);
            $intersect = array_intersect_key($params, $match);
            $params = array_diff_key($params, $match);
            $captureParams = [];
            foreach ($intersect as $key => &$value) {
                $value = strtolower($value);
                $captureParams['<' . $key . '>'] = trim($value, '/');
            }
            $handler = strtr($handler, $captureParams);
        }
        return [true, $handler, $params];
    }
}
