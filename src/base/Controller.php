<?php

declare(strict_types=1);

namespace Ep\base;

use RuntimeException;
use Ep\Standard\ViewInterface;
use Ep\Standard\ContextInterface;
use Ep\Standard\ControllerInterface;

abstract class Controller implements ControllerInterface, ContextInterface
{
    public function run(string $actionName, $request)
    {
        if (!is_callable([$this, $actionName])) {
            throw new RuntimeException(sprintf('%s::%s() is not found.', get_class($this), $actionName));
        }
        if ($this->beforeAction($request)) {
            $response = call_user_func([$this, $actionName], $request);
            return $this->afterAction($response);
        }
        return null;
    }

    protected function setLayout(string $layout): void
    {
        $this->getView()->setLayout($layout);
    }

    protected abstract function beforeAction($request): bool;

    protected abstract function afterAction($response);

    protected abstract function getView(): ViewInterface;
}
