<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Contract\ConfigurableInterface;
use Ep\Contract\ConfigurableTrait;
use Ep\Contract\ControllerInterface;
use Ep\Contract\ModuleInterface;
use Ep\Contract\NotFoundException;
use Ep\Helper\Str;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use InvalidArgumentException;

class ControllerRunner implements ConfigurableInterface
{
    use ConfigurableTrait;

    protected Config $config;
    protected ContainerInterface $container;
    protected Injector $injector;
    protected string $suffix;

    public function __construct(ContainerInterface $container, Injector $injector)
    {
        $this->config = Ep::getConfig();
        $this->container = $container;
        $this->injector = $injector;
        $this->suffix = $this->config->controllerDirAndSuffix;
    }

    /**
     * @param  mixed $handler
     * @param  mixed $request
     * 
     * @return mixed
     * @throws NotFoundException
     */
    public function run($handler, $request)
    {
        [$prefix, $class, $action] = $this->parseHandler($handler);

        $module = $this->createModule($prefix);

        $controller = $this->createController($class);
        $controller->actionId = $action;
        $action .= $this->config->actionSuffix;

        if ($module instanceof ModuleInterface) {
            return $this->runModule($module, $controller, $action, $request);
        } else {
            return $this->runAction($controller, $action, $request);
        }
    }

    private function createModule(string $prefix): ?ModuleInterface
    {
        $prefix = str_replace('/', '\\', $prefix);
        if (strpos($prefix, '\\\\') !== false) {
            $prefix = explode('\\\\', trim($prefix, '\\'))[0];
        }
        $moduleClass = $this->config->appNamespace . '\\' . ($prefix ? $prefix . '\\' : '') . $this->config->moduleName;
        if (class_exists($moduleClass)) {
            return $this->container->get($moduleClass);
        } else {
            return null;
        }
    }

    private function createController(string $class): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new NotFoundException("{$class} is not found.");
        }
        $controller = $this->container->get($class);
        $controller->id = $this->generateContextId($controller);
        return $controller;
    }

    /**
     * @param  mixed $request
     * 
     * @return mixed
     */
    protected function runModule(ModuleInterface $module, ControllerInterface $controller, string $action, $request)
    {
        $response = $module->before($request);
        if ($response === true) {
            return $module->after($request, $this->runAction($controller, $action, $request));
        } else {
            return $response;
        }
    }

    /**
     * @param  mixed $request
     * 
     * @return mixed
     */
    protected function runAction(ControllerInterface $controller, string $action, $request)
    {
        if (!is_callable([$controller, $action])) {
            throw new NotFoundException(sprintf('%s::%s() is not found.', get_class($controller), $action));
        }
        $response = $controller->before($request);
        if ($response === true) {
            return $controller->after($request, $this->injector->invoke([$controller, $action], [$request]));
        } else {
            return $response;
        }
    }

    /**
     * @param mixed $handler
     */
    private function parseHandler($handler): array
    {
        switch (gettype($handler)) {
            case 'array':
                return $this->parseArrayHandler($handler);
            case 'string':
                return $this->parseStringHandler($handler);
            default:
                throw new InvalidArgumentException('The route handler is invalid.');
        }
    }

    private function parseArrayHandler(array $handler): array
    {
        switch (count($handler)) {
            case 1:
                array_push($handler, $this->config->defaultAction);
            case 2:
                array_unshift($handler, str_replace(
                    $this->config->appNamespace . '\\',
                    '',
                    substr($handler[0], 0, strpos($handler[0], $this->suffix) - 1)
                ));
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
        if ($prefix) {
            $ns = strpos($prefix, '\\\\') === false ? $prefix . '\\' . $this->suffix : str_replace('\\\\', '\\' . $this->suffix . '\\', $prefix);
        } else {
            $ns = $this->suffix;
        }
        $class = sprintf('%s\\%s\\%s', $this->config->appNamespace, $ns, Str::toPascalCase($controller) . $this->suffix);
        return [$prefix, $class, $action];
    }

    private function generateContextId(ControllerInterface $controller): string
    {
        return implode('/', array_filter(
            array_map('lcfirst', explode(
                '\\',
                str_replace([$this->config->appNamespace, $this->suffix], '', get_class($controller))
            ))
        ));
    }
}