<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ControllerInterface;
use Ep\Contract\InjectorInterface;
use Ep\Contract\ModuleInterface;
use Ep\Contract\NotFoundException;
use Psr\Container\ContainerInterface;

abstract class ControllerRunner
{
    protected ContainerInterface $container;
    protected Config $config;
    protected InjectorInterface $injector;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Config::class);
        $this->injector = $container->get(InjectorInterface::class);
    }

    /**
     * @param  mixed $handler
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     * @throws NotFoundException
     */
    public function run($handler, $request, $response = null)
    {
        return $this->runLoader(
            $this->injector
                ->make(ControllerLoader::class, [
                    'suffix' => $this->getControllerSuffix()
                ])
                ->parse($handler),
            $request,
            $response
        );
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     */
    public function runLoader(ControllerLoader $loader, $request, $response = null)
    {
        return $this->runAll($loader->getModule(), $loader->getController(), $loader->getAction(), $request, $response);
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     */
    public function runAll(?ModuleInterface $module, ControllerInterface $controller, string $action, $request, $response = null)
    {
        if ($module instanceof ModuleInterface) {
            return $this->runModule($module, $controller, $action, $request, $response);
        } else {
            return $this->runAction($controller, $action, $request, $response);
        }
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     */
    protected function runModule(ModuleInterface $module, ControllerInterface $controller, string $action, $request, $response = null)
    {
        $result = $module->before($request, $response);
        if ($result === true) {
            return $module->after($request, $this->runAction($controller, $action, $request, $response));
        } else {
            return $result;
        }
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     */
    protected function runAction(ControllerInterface $controller, string $action, $request, $response = null)
    {
        if (!is_callable([$controller, $action])) {
            throw new NotFoundException(sprintf('%s::%s() is not found.', get_class($controller), $action));
        }
        $result = $controller->before($request, $response);
        if ($result === true) {
            return $controller->after($request, $this->injector->call($controller, $action, array_filter([$request, $response])));
        } else {
            return $result;
        }
    }

    abstract public function getControllerSuffix(): string;
}
