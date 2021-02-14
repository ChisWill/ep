<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Standard\ViewInterface;
use Ep\Standard\ContextInterface;
use Ep\Standard\ControllerInterface;
use RuntimeException;

abstract class Controller implements ControllerInterface, ContextInterface
{
    /**
     * {@inheritDoc}
     */
    public function run(string $action, $request)
    {
        $action .= Ep::getConfig()->actionSuffix;
        if (!is_callable([$this, $action])) {
            throw new RuntimeException(sprintf('%s::%s() is not found.', get_class($this), $action));
        }
        if ($this->beforeAction($request)) {
            $response = Ep::getInjector()->invoke([$this, $action], [$request]);
            return $this->afterAction($request, $response);
        }
    }

    private ?string $id = null;

    /**
     * {@inheritDoc}
     */
    public function getId(bool $short = true): string
    {
        if ($short === false) {
            return static::class;
        }
        if ($this->id === null) {
            $config = Ep::getConfig();
            $this->id = implode('/', array_filter(
                array_map('lcfirst', explode(
                    '\\',
                    str_replace([$config->appNamespace, PHP_SAPI === 'cli' ? $config->commandDirAndSuffix : $config->controllerDirAndSuffix], '', static::class)
                ))
            ));
        }
        return $this->id;
    }

    abstract protected function beforeAction($request): bool;

    abstract protected function afterAction($request, $response);

    abstract protected function getView(): ViewInterface;
}
