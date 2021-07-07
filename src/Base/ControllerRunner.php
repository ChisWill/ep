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
     * 
     * @return mixed
     * @throws NotFoundException
     */
    public function run($handler, $request)
    {
        return $this->runLoader(
            $this->injector
                ->make(ControllerLoader::class, [
                    'suffix' => $this->getControllerSuffix()
                ])
                ->parse($handler),
            $request
        );
    }

    /**
     * @param  mixed $request
     * 
     * @return mixed
     */
    public function runLoader(ControllerLoader $loader, $request)
    {
        return $this->runAll($loader->getModule(), $loader->getController(), $loader->getAction(), $request);
    }

    /**
     * @param  mixed $request
     * 
     * @return mixed
     */
    private function runAll(?ModuleInterface $module, ControllerInterface $controller, string $action, $request)
    {
        if ($module instanceof ModuleInterface) {
            return $this->runModule($module, $controller, $action, $request);
        } else {
            return $this->runAction($controller, $action, $request);
        }
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
            return $controller->after($request, $this->injector->call($controller, $action, [$request]));
        } else {
            return $response;
        }
    }

    abstract public function getControllerSuffix(): string;
}
