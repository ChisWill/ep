<?php

declare(strict_types=1);

namespace Ep\base;

use Ep;
use Ep\Standard\ViewInterface;
use Ep\Standard\ContextInterface;
use Ep\Standard\ControllerInterface;
use Yiisoft\Strings\StringHelper;
use RuntimeException;

abstract class Controller implements ControllerInterface, ContextInterface
{
    /**
     * @inheritDoc
     */
    public function run(string $action, $request)
    {
        $action .= Ep::getConfig()->actionSuffix;
        if (!is_callable([$this, $action])) {
            throw new RuntimeException(sprintf('%s::%s() is not found.', get_class($this), $action));
        }
        if ($this->beforeAction($request)) {
            $response = Ep::getInjector()->invoke([$this, $action], [$request]);
            return $this->afterAction($response);
        }
    }

    private ?string $id = null;

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        if ($this->id === null) {
            $this->id = lcfirst(StringHelper::baseName(get_class($this), Ep::getConfig()->controllerDirAndSuffix));
        }
        return $this->id;
    }

    protected abstract function beforeAction($request): bool;

    protected abstract function afterAction($response);

    protected abstract function getView(): ViewInterface;
}
