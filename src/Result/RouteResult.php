<?php

declare(strict_types=1);

namespace Ep\Result;

use Ep\Base\Config;
use Ep\Contract\BootstrapInterface;
use Ep\Helper\Str;
use Doctrine\Common\Annotations\Annotation\Target;
use Yiisoft\Http\Method;
use FastRoute\RouteCollector;
use Closure;

final class RouteResult implements BootstrapInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    private array $rules = [];

    public function bootstrap(array $data = []): void
    {
        if ($this->config->rootNamespace === 'Ep') {
            return;
        }

        foreach ($data as $class => $value) {
            if (!isset($value[Target::TARGET_METHOD])) {
                continue;
            }

            if (isset($value[Target::TARGET_CLASS])) {
                $path = rtrim($value[Target::TARGET_CLASS]['value'], '/') . '/';
                $method = $value[Target::TARGET_CLASS]['method'] ?? Method::GET;
            } else {
                $path = '/';
                $method = Method::GET;
            }

            foreach ($value[Target::TARGET_METHOD] as $item) {
                $this->rules[$item['method'] ?? $method]['/' . trim($path . trim($item['value'], '/'), '/')] = [$class, Str::rtrim($item['target'], $this->config->actionSuffix)];
            }
        }
    }

    public function getRouteRule(): Closure
    {
        return function (RouteCollector $route): void {
            foreach ($this->rules as $method => $value) {
                foreach ($value as $path => $handler) {
                    $route->addRoute($method, $path, $handler);
                }
            }
        };
    }
}
