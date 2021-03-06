<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Base\ControllerRunner as BaseControllerRunner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Closure;

final class ControllerRunner extends BaseControllerRunner
{
    /**
     * @param  ServerRequestInterface $request
     * 
     * @return ResponseInterface
     */
    protected function runAction(Controller $controller, string $action, $request)
    {
        $middlewares = $controller->getMiddlewares();
        if ($middlewares) {
            $requestHandlerFactory = $this->container->get(RequestHandlerFactory::class);
            return $requestHandlerFactory
                ->wrap($middlewares, $requestHandlerFactory->create($this->wrap($controller, $action)))
                ->handle($request);
        } else {
            return parent::runAction($controller, $action, $request);
        }
    }

    private function wrap(Controller $controller, string $action): Closure
    {
        return fn (ServerRequestInterface $request) => parent::runAction($controller, $action, $request);
    }
}
