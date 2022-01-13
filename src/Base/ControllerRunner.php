<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Base\ControllerLoaderResult;
use Ep\Contract\ControllerInterface;
use Ep\Contract\InjectorInterface;
use Ep\Contract\ModuleInterface;
use Ep\Event\AfterRequest;
use Ep\Event\BeforeRequest;
use Ep\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class ControllerRunner
{
    protected ContainerInterface $container;
    protected Config $config;
    protected InjectorInterface $injector;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Config::class);
        $this->injector = $container->get(InjectorInterface::class);
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
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
        return $this->runResult(
            $this->container
                ->get(ControllerLoader::class)
                ->withSuffix($this->getControllerSuffix())
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
    public function runResult(ControllerLoaderResult $result, $request, $response = null)
    {
        return $this->runAll($result->getModule(), $result->getController(), $result->getAction(), $request, $response);
    }

    /**
     * @param  mixed $request
     * @param  mixed $response
     * 
     * @return mixed
     */
    public function runAll(?ModuleInterface $module, ControllerInterface $controller, string $action, $request, $response = null)
    {
        $request = $this->eventDispatcher->dispatch(new BeforeRequest($request, $response))->getRequest();

        try {
            if ($module instanceof ModuleInterface) {
                return $result = $this->runModule($module, $controller, $action, $request, $response);
            } else {
                return $result = $this->runAction($controller, $action, $request, $response);
            }
        } finally {
            $this->eventDispatcher->dispatch(new AfterRequest($request, $result ?? null));
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
