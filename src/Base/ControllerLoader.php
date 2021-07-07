<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ControllerInterface;
use Ep\Contract\ModuleInterface;
use Ep\Contract\NotFoundException;
use Ep\Helper\Str;
use Psr\Container\ContainerInterface;
use InvalidArgumentException;
use LogicException;

final class ControllerLoader
{
    private string $suffix;
    private ContainerInterface $container;
    private Config $config;

    public function __construct(
        string $suffix,
        ContainerInterface $container,
        Config $config
    ) {
        $this->suffix = $suffix;
        $this->container = $container;
        $this->config = $config;
    }

    private ?ModuleInterface $module;
    private ControllerInterface $controller;
    private string $action;

    /**
     * @param  mixed $handler
     * 
     * @throws NotFoundException
     */
    public function parse($handler): self
    {
        $new = clone $this;

        [$prefix, $class, $actionId] = $new->parseHandler($handler);

        $new->module = $new->createModule($prefix);
        $new->controller = $new->createController($class, $actionId);
        $new->action = $new->createAction($actionId);

        return $new;
    }

    /**
     * @param  string|array $handler
     * 
     * @throws LogicException
     */
    public function parseHandler($handler): array
    {
        switch (gettype($handler)) {
            case 'string':
                return $this->parseStringHandler($handler);
            case 'array':
                return $this->parseArrayHandler($handler);
            default:
                throw new LogicException('The route handler is invalid.');
        }
    }

    public function getModule(): ?ModuleInterface
    {
        return $this->module;
    }

    public function getController(): ControllerInterface
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    private function createModule(string $prefix): ?ModuleInterface
    {
        $prefix = str_replace('/', '\\', $prefix);
        if (strpos($prefix, '\\\\') !== false) {
            $prefix = explode('\\\\', trim($prefix, '\\'))[0];
        }
        $class = $this->config->rootNamespace . '\\' . ($prefix ? $prefix . '\\' : '') . $this->suffix . '\\' . $this->config->moduleName;
        if (class_exists($class)) {
            return $this->container->get($class);
        } else {
            return null;
        }
    }

    private function createController(string $class, string $actionId): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new NotFoundException("{$class} is not found.");
        }

        return $this->container
            ->get($class)
            ->configure([
                'id' => $this->generateContextId($class),
                'actionId' => $actionId
            ]);
    }

    private function createAction(string $actionId): string
    {
        return $actionId . $this->config->actionSuffix;
    }

    private function parseArrayHandler(array $handler): array
    {
        switch (count($handler)) {
            case 1:
                array_push($handler, $this->config->defaultAction);
            case 2:
                $suffixPos = strpos($handler[0], '\\' . $this->suffix . '\\');
                if ($suffixPos === false) {
                    throw new InvalidArgumentException('The route handler is not in the correct directory.');
                }
                array_unshift($handler, str_replace($this->config->rootNamespace . '\\', '', substr($handler[0], 0, $suffixPos)));
                break;
            case 3:
                break;
            default:
                throw new InvalidArgumentException('The route handler is not in the correct format.');
        }
        return $handler;
    }

    private function parseStringHandler(string $handler): array
    {
        $pieces = explode('/', $handler);
        $prefix = '';
        switch (count($pieces)) {
            case 0:
                $controller = $this->config->defaultController;
                $action = $this->config->defaultAction;
                break;
            case 1:
                $controller = $pieces[0];
                $action = $this->config->defaultAction;
                break;
            default:
                $action = array_pop($pieces) ?: $this->config->defaultAction;
                $controller = array_pop($pieces) ?: $this->config->defaultController;
                $prefix = implode('\\', array_map([Str::class, 'toPascalCase'], $pieces));
                break;
        }

        $class = sprintf(
            '%s\\%s\\%s',
            $this->config->rootNamespace,
            $prefix ? (strpos($prefix, '\\\\') === false ? $prefix . '\\' . $this->suffix : str_replace('\\\\', '\\' . $this->suffix . '\\', $prefix)) : $this->suffix,
            Str::toPascalCase($controller) . $this->suffix
        );
        $action = lcfirst(Str::toPascalCase($action));

        return [$prefix, $class, $action];
    }

    private function generateContextId(string $class): string
    {
        return implode('/', array_filter(
            array_map('lcfirst', explode(
                '\\',
                str_replace([$this->config->rootNamespace, $this->suffix], '', $class)
            ))
        ));
    }
}
