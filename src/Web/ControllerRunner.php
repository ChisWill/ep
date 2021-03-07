<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\ControllerRunner as BaseControllerRunner;
use Ep\Contract\ControllerInterface;
use Ep\Contract\ModuleInterface;
use Psr\Http\Message\ServerRequestInterface;
use Closure;

final class ControllerRunner extends BaseControllerRunner
{
    /**
     * @param  Controller             $controller
     * @param  ServerRequestInterface $request
     * 
     * @return mixed
     */
    protected function runModule(ModuleInterface $module, ControllerInterface $controller, string $action, $request)
    {
        $middlewares = $module->getMiddlewares();
        if ($middlewares) {
            $requestHandlerFactory = $this->container->get(RequestHandlerFactory::class);
            return $this->container->get(RequestHandlerFactory::class)
                ->wrap($middlewares, $requestHandlerFactory->create($this->wrapModule($module, $controller, $action)))
                ->handle($request);
        } else {
            return parent::runModule($module, $controller, $action, $request);
        }
    }

    /**
     * @param  Controller             $controller
     * @param  ServerRequestInterface $request
     * 
     * @return mixed
     */
    protected function runAction(ControllerInterface $controller, string $action, $request)
    {
        $middlewares = $controller->getMiddlewares();
        if ($middlewares) {
            $requestHandlerFactory = $this->container->get(RequestHandlerFactory::class);
            return $this->container->get(RequestHandlerFactory::class)
                ->wrap($middlewares, $requestHandlerFactory->create($this->wrapController($controller, $action)))
                ->handle($request);
        } else {
            return parent::runAction($controller, $action, $request);
        }
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
