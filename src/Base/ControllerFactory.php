<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Contract\ConfigurableInterface;
use Ep\Contract\ControllerInterface;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
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
        [$class, $action] = $this->parseHandler($handler);

        $controller = $this->create($class);
        $controller->id = $this->getContextId($controller);

        return $this->runAction($controller, $action, $request);
    }

    private function create(string $class): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new UnexpectedValueException("{$class} is not found.");
        }
        return $this->container->get($class);
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
        if (is_array($handler)) {
            return $handler;
        }
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
        return [$class, $action];
    }

    private function getContextId(ControllerInterface $controller): string
    {
        return implode('/', array_filter(
            array_map('lcfirst', explode(
                '\\',
                str_replace([$this->config->appNamespace, $this->suffix], '', get_class($controller))
            ))
        ));
    }
}
