<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\BootstrapInterface;
use Ep\Exception\NotFoundException;
use Ep\Helper\Str;
use Doctrine\Common\Annotations\Annotation\Target;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Http\Method;
use Closure;

use function FastRoute\cachedDispatcher;

final class Route implements BootstrapInterface
{
    private Config $config;
    private Aliases $aliases;

    public function __construct(
        Config $config,
        Aliases $aliases
    ) {
        $this->config = $config;
        $this->aliases = $aliases;
    }

    private array $annotationRules = [];

    public function bootstrap(array $data = []): void
    {
        foreach ($data as $class => $value) {
            if (!isset($value[Target::TARGET_METHOD])) {
                continue;
            }

            if (isset($value[Target::TARGET_CLASS])) {
                $path = rtrim($value[Target::TARGET_CLASS]['value'], '/') . '/';
                $method = (array) ($value[Target::TARGET_CLASS]['method'] ?? Method::GET);
            } else {
                $path = '/';
                $method = [Method::GET];
            }

            foreach ($value[Target::TARGET_METHOD] as $item) {
                $this->annotationRules['/' . trim($path . trim($item['value'], '/'), '/')] = [
                    (array) ($item['method'] ?? $method),
                    [$class, Str::rtrim($item['target'], $this->config->actionSuffix)]
                ];
            }
        }
    }

    private bool $enableDefaultRule = true;

    public function withEnableDefaultRule(bool $enableDefaultRule): self
    {
        $new = clone $this;
        $new->enableDefaultRule = $enableDefaultRule;
        return $new;
    }

    private array $defaultRule = [
        Method::ALL,
        '{prefix:[\w/-]*?}{controller:/?[a-zA-Z][\w-]*|}{action:/?[a-zA-Z][\w-]*|}',
        '<prefix>/<controller>/<action>'
    ];

    public function withDefaultRule(array $rule): self
    {
        $new = clone $this;
        $new->defaultRule = $rule;
        return $new;
    }

    private string $baseUrl = '';

    public function withBaseUrl(string $baseUrl): self
    {
        $new = clone $this;
        $new->baseUrl = $baseUrl;
        return $new;
    }

    private ?Closure $rule = null;

    public function withRule(Closure $rule): self
    {
        $new = clone $this;
        $new->rule = $rule;
        return $new;
    }

    /**
     * @throws NotFoundException
     */
    public function match(string $path, string $method = Method::GET): array
    {
        return $this->solveRouteInfo(
            cachedDispatcher(function (RouteCollector $route): void {
                if ($this->rule) {
                    $route->addGroup($this->baseUrl, $this->rule);
                }

                $route->addGroup($this->baseUrl, $this->getAnnotationRule());

                if ($this->enableDefaultRule) {
                    $route->addGroup($this->baseUrl, fn (RouteCollector $r) => $r->addRoute(...$this->defaultRule));
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

    private function getAnnotationRule(): Closure
    {
        return function (RouteCollector $route): void {
            foreach ($this->annotationRules as $path => [$method, $handler]) {
                $route->addRoute($method, $path, $handler);
            }
        };
    }
}
