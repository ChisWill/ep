<?php

namespace ep;

use ep\base\Config;
use ep\web\Config as WebConfig;
use ep\web\Exception;
use ep\web\Request;

class Core
{
    public function __construct()
    {
        require_once 'functions.php';
    }

    public function run(Config $config)
    {
        switch (get_class($config)) {
            case 'ep\web\Config':
                $this->handleWebRoute($config);
                break;
            case 'ep\console\Config':
                break;
        }
    }

    private function handleWebRoute(WebConfig $config)
    {
        $request = new Request($config);
        $requestPath = $request->getRequestPath();
        if (count($config->routeRules) > 0) {
            $requestPath = $request->solveRouteRules($config->routeRules, $requestPath);
        }
        [$controllerName, $actionName] = $request->solvePath($requestPath);
        if (!class_exists($controllerName)) {
            throw new Exception('NOT FOUND', 404);
        }
        $controller = new $controllerName;
        if (!method_exists($controller, $actionName)) {
            throw new Exception('NOT FOUND', 404);
        }
        $controller->$actionName($request);
    }
}
