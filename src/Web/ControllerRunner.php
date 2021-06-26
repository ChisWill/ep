<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\ControllerRunner as BaseControllerRunner;
use Ep\Contract\ControllerInterface;
use Ep\Contract\ModuleInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Closure;

final class ControllerRunner extends BaseControllerRunner
{
    private RequestHandlerFactory $requestHandlerFactory;
    private Service $service;

    public function __construct(ContainerInterface $container, RequestHandlerFactory $requestHandlerFactory, Service $service)
    {
        parent::__construct($container);

        $this->requestHandlerFactory = $requestHandlerFactory;
        $this->service = $service;
    }

    protected function getControllerSuffix(): string
    {
        return $this->config->controllerDirAndSuffix;
    }

    /**
     * @param  ServerRequestInterface $request
     * 
     * @return mixed
     */
    protected function runModule(ModuleInterface $module, ControllerInterface $controller, string $action, $request)
    {
        $middlewares = $module->getMiddlewares();
        if ($middlewares) {
            return $this->requestHandlerFactory
                ->wrap($middlewares, $this->requestHandlerFactory->create($this->wrapModule($module, $controller, $action)))
                ->handle($request);
        } else {
            return parent::runModule($module, $controller, $action, $request);
        }
    }

    /**
     * @param  ServerRequestInterface $request
     * 
     * @return mixed
     */
    protected function runAction(ControllerInterface $controller, string $action, $request)
    {
        $middlewares = $controller->getMiddlewares();
        if ($middlewares) {
            return $this->requestHandlerFactory
                ->wrap($middlewares, $this->requestHandlerFactory->create($this->wrapController($controller, $action)))
                ->handle($request);
        } else {
            return parent::runAction($controller, $action, $request);
        }
    }

    /**
     * @param  ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    protected function call(ControllerInterface $controller, string $action, $request)
    {
        return $this->service->toResponse(parent::call($controller, $action, $request));
    }

    private function wrapModule(ModuleInterface $module, Controller $controller, string $action): Closure
    {
        return fn (ServerRequestInterface $request) => parent::runModule($module, $controller, $action, $request);
    }

    private function wrapController(Controller $controller, string $action): Closure
    {
        return fn (ServerRequestInterface $request) => parent::runAction($controller, $action, $request);
    }
}
