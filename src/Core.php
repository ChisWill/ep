<?php

namespace ep;

use Ep\base\Config;
use Ep\console\Config as ConsoleConfig;
use Ep\helper\Ep;
use Ep\web\Config as WebConfig;
use Ep\web\Controller;
use Ep\Exception;
use Ep\helper\Alias;
use Ep\web\Request;
use Ep\web\Response;

final class Core
{
    public function __construct(string $rootPath)
    {
        require_once 'functions.php';

        $this->setDefaultAlias($rootPath);
        $this->setExceptionHandler();
    }

    private function setDefaultAlias(string $rootPath)
    {
        Alias::set('@root', $rootPath);
        Alias::set('@ep', __DIR__);
    }

    private function setExceptionHandler()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new Exception(sprintf('%s, in %s:%d', $errstr, $errfile, $errline));
        }, E_ALL);
    }

    public function run(Config $config): void
    {
        Ep::init($config);

        switch (get_parent_class($config)) {
            case 'Ep\web\Config':
                $response = $this->handleWebRoute($config);
                if ($response) {
                    $response->sendContent();
                }
                break;
            case 'Ep\console\Config':
                $r = $this->handleConsoleRoute($config);
                break;
        }
    }

    private function handleConsoleRoute(ConsoleConfig $config)
    {
    }

    private function handleWebRoute(WebConfig $config): ?Response
    {
        /** @var Request $request */
        $request = Ep::getDi()->get('request');
        $requestPath = $request->getRequestPath();
        if (count($config->routeRules) > 0) {
            $requestPath = $request->solveRouteRules($config->routeRules, $requestPath);
        }
        [$controllerName, $actionName] = $request->solvePath($requestPath);
        if (!class_exists($controllerName)) {
            throw new Exception(Exception::NOT_FOUND_CTRL);
        }
        /** @var Controller $controller */
        $controller = new $controllerName;
        if (!method_exists($controller, $actionName)) {
            throw new Exception(Exception::NOT_FOUND_ACTION);
        }
        if (in_array($actionName, ['beforeAction', 'afterAction'])) {
            throw new Exception(Exception::NOT_FOUND_ACTION);
        }
        if ($controller->beforeAction()) {
            $response = $controller->$actionName($request, Ep::getDi()->get('response'));
            $controller->afterAction($response);
            return $response;
        } else {
            return null;
        }
    }
}
