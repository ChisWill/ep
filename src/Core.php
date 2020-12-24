<?php

namespace ep;

use ep\base\Config;
use ep\helper\Ep;
use ep\web\Config as WebConfig;
use ep\web\Controller;
use ep\Exception;
use ep\web\Request;
use ep\web\Response;
use Yiisoft\Di\Container;

final class Core
{
    public function __construct(string $rootPath)
    {
        require_once 'functions.php';

        Ep::setAlias('@root', $rootPath);
        Ep::setAlias('@ep', __DIR__);

        $this->setExceptionHandler();
    }

    private function setExceptionHandler()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new Exception(sprintf('%s, in %s:%d', $errstr, $errfile, $errline));
        }, E_ALL);
    }

    public function run(?Config $config = null, ?Container $container = null): void
    {
        Ep::init($config, $container);

        switch (get_parent_class($config)) {
            case 'ep\web\Config':
                $response = $this->handleWebRoute($config);
                $response->sendContent();
                break;
            case 'ep\console\Config':
                break;
        }
    }

    private function handleWebRoute(WebConfig $config): Response
    {
        /** @var Request $request */
        $request = Ep::getDi()->get('webRequest');
        $requestPath = $request->getRequestPath();
        if (count($config->routeRules) > 0) {
            $requestPath = $request->solveRouteRules($config->routeRules, $requestPath);
        }
        [$controllerName, $actionName] = $request->solvePath($requestPath);
        if (!class_exists($controllerName)) {
            throw new Exception('NOT FOUND', 404);
        }
        /** @var Controller $controller */
        $controller = new $controllerName;
        if (!method_exists($controller, $actionName)) {
            throw new Exception('NOT FOUND', 404);
        }
        return $controller->$actionName($request, Ep::getDi()->get('webResponse'));
    }
}
