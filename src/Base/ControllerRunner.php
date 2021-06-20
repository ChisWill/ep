<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ConfigurableInterface;
use Ep\Contract\ConfigurableTrait;
use Ep\Contract\ControllerInterface;
use Ep\Contract\InjectorInterface;
use Ep\Contract\ModuleInterface;
use Ep\Contract\NotFoundException;
use Ep\Helper\Str;
use Psr\Container\ContainerInterface;
use InvalidArgumentException;

class ControllerRunner implements ConfigurableInterface
{
    use ConfigurableTrait;

    protected ?string $suffix = null;

    private ContainerInterface $container;
    private Config $config;
    private InjectorInterface $injector;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Config::class);
        $this->injector = $container->get(InjectorInterface::class);
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
        [$prefix, $class, $actionId] = $this->parseHandler($handler);

        $module = $this->createModule($prefix);

        $controller = $this->createController($class, $actionId, $request);

        $action = $this->createAction($actionId);

        if ($module instanceof ModuleInterface) {
            return $this->runModule($module, $controller, $action, $request);
        } else {
            return $this->runAction($controller, $action, $request);
        }
    }

    protected function createModule(string $prefix): ?ModuleInterface
    {
        $prefix = str_replace('/', '\\', $prefix);
        if (strpos($prefix, '\\\\') !== false) {
            $prefix = explode('\\\\', trim($prefix, '\\'))[0];
        }
        $class = $this->config->appNamespace . '\\' . ($prefix ? $prefix . '\\' : '') . $this->suffix . '\\' . $this->config->moduleName;
        if (class_exists($class)) {
            return $this->container->get($class);
        } else {
            return null;
        }
    }

    protected function createController(string $class, string $actionId, $request): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new NotFoundException("{$class} is not found.");
        }

        $controller = $this->container
            ->get($class)
            ->clone([
                'id' => $this->generateContextId($class),
                'actionId' => $actionId
            ]);

        return $controller;
    }

    protected function createAction(string $actionId): string
    {
        return $actionId . $this->config->actionSuffix;
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
            return $controller->after($request, $this->call($controller, $action, $request));
        } else {
            return $response;
        }
    }

    /**
     * @param  mixed $request
     * 
     * @return mixed
     */
    protected function call(ControllerInterface $controller, string $action, $request)
    {
        return $this->injector->call($controller, $action, [$request]);
    }

    /**
     * @param string|array $handler
     */
    private function parseHandler($handler): array
    {
        switch (gettype($handler)) {
            case 'string':
                return $this->parseStringHandler($handler);
            case 'array':
                return $this->parseArrayHandler($handler);
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
                $suffixPos = strpos($handler[0], '\\' . $this->getControllerSuffix() . '\\');
                if ($suffixPos === false) {
                    throw new InvalidArgumentException('The route handler is not in the correct directory.');
                }
                array_unshift($handler, str_replace($this->config->appNamespace . '\\', '', substr($handler[0], 0, $suffixPos)));
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
        $suffix = $this->getControllerSuffix();

        $class = sprintf(
            '%s\\%s\\%s',
            $this->config->appNamespace,
            $prefix ? (strpos($prefix, '\\\\') === false ? $prefix . '\\' . $suffix : str_replace('\\\\', '\\' . $suffix . '\\', $prefix)) : $suffix,
            Str::toPascalCase($controller) . $suffix
        );
        return [$prefix, $class, $action];
    }

    private function getControllerSuffix(): string
    {
        if ($this->suffix === null) {
            $this->suffix = $this->config->controllerDirAndSuffix;
        }
        return $this->suffix;
    }

    private function generateContextId(string $class): string
    {
        return implode('/', array_filter(
            array_map('lcfirst', explode(
                '\\',
                str_replace([$this->config->appNamespace, $this->getControllerSuffix()], '', $class)
            ))
        ));
    }
}
