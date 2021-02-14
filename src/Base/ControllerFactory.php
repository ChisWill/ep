<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Standard\ControllerInterface;
use RuntimeException;

final class ControllerFactory
{
    private Config $config;
    private string $suffix;

    public function __construct()
    {
        $this->config = Ep::getConfig();
        $this->suffix = PHP_SAPI === 'cli' ? $this->config->commandDirAndSuffix : $this->config->controllerDirAndSuffix;
    }

    /**
     * @param string|array $hander
     */
    public function run($handler, $request)
    {
        [$class, $action] = $this->parseHandler($handler);

        $controller = $this->create($class);

        return $controller->run($action, $request);
    }

    private function create(string $class): ControllerInterface
    {
        if (!class_exists($class)) {
            throw new RuntimeException("{$class} is not found.");
        }
        return Ep::getDi()->get($class);
    }

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
}
