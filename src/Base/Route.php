<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ConfigurableInterface;
use Ep\Contract\ConfigurableTrait;
use Ep\Contract\NotFoundException;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Http\Method;
use Closure;

use function FastRoute\cachedDispatcher;

final class Route implements ConfigurableInterface
{
    use ConfigurableTrait;

    protected bool $defaultRoute = true;
    protected string $baseUrl = '';
    protected Closure $rule;

    private Config $config;
    private Aliases $aliases;

    public function __construct(Config $config, Aliases $aliases)
    {
        $this->config = $config;
        $this->aliases = $aliases;
    }

    /**
     * @throws NotFoundException
     */
    public function match(string $path, string $method = Method::GET): array
    {
        return $this->solveRouteInfo(
            cachedDispatcher(function (RouteCollector $route): void {
                if (isset($this->rule)) {
                    $route->addGroup($this->baseUrl, $this->rule);
                }
                if ($this->defaultRoute) {
                    $route->addGroup($this->baseUrl, fn (RouteCollector $r) => $r->addRoute(...$this->config->getDefaultRoute()));
                }
            }, [
                'cacheFile' => $this->aliases->get($this->config->runtimeDir . '/route.cache'),
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
