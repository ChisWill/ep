<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Contract\ConfigurableInterface;
use Ep\Contract\ControllerInterface;
use Ep\Contract\ModuleInterface;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use InvalidArgumentException;
use UnexpectedValueException;

final class ControllerFactory implements ConfigurableInterface
{
    use ConfigurableTrait;

    private Config $config;
    private string $suffix;
    private ContainerInterface $container;
    private Injector $injector;

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
     * @throws UnexpectedValueException
     */
    public function run($handler, $request)
    {
        [$prefix, $class, $action] = $this->parseHandler($handler);

        $this->runModule($prefix, $request);

        $controller = $this->create($class);

        return $this->runAction($controller, $action, $request);
    }

    private function create(string $class): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new UnexpectedValueException("{$class} is not found.");
        }
        $controller = $this->container->get($class);
        $controller->id = $this->generateContextId($controller);
        return $controller;
    }

    private function runModule(string $prefix, $request): void
    {
        $prefix = str_replace('/', '\\', $prefix);
        if (strpos($prefix, '\\\\') !== false) {
            $prefix = explode('\\\\', $prefix)[0];
        }
        $moduleClass = $this->config->appNamespace . '\\' . ($prefix ? $prefix . '\\' : '') . $this->config->moduleName;
        if (class_exists($moduleClass)) {
            $module = $this->container->get($moduleClass);
            if (!$module instanceof ModuleInterface) {
                throw new InvalidArgumentException("The class {$moduleClass} must implement the interface Ep\Contract\ModuleInterface.");
            }
            $module->bootstrap($request);
        }
    }

    /**
     * @param  mixed $request
     * 
     * @return mixed
     */
    private function runAction(ControllerInterface $controller, string $action, $request)
    {
        $action .= $this->config->actionSuffix;
        if (!is_callable([$controller, $action])) {
            throw new UnexpectedValueException(sprintf('%s::%s() is not found.', get_class($controller), $action));
        }
        $response = $controller->before($request);
        if ($response === true) {
            $response = $this->injector->invoke([$controller, $action], [$request]);
            $response = $controller->after($request, $response);
        }
        return $response;
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
            case 0:
                $handler = ['', $this->config->defaultController, $this->config->defaultAction];
                break;
            case 1:
                array_push($handler, $this->config->defaultAction);
            case 2:
                array_unshift($handler, '');
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
                $prefix = implode('\\', $pieces);
                break;
        }
        if ($prefix) {
            $ns = strpos($prefix, '\\\\') === false ? $prefix . '\\' . $this->suffix : str_replace('\\\\', '\\' . $this->suffix . '\\', $prefix);
        } else {
            $ns = $this->suffix;
        }
        $class = sprintf('%s\\%s\\%s', $this->config->appNamespace, $ns, ucfirst($controller) . $this->suffix);
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
