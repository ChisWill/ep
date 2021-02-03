<?php

declare(strict_types=1);

namespace Ep\base;

use Ep;
use RuntimeException;
use Ep\Standard\ViewInterface;
use Ep\Standard\ContextInterface;
use Ep\Standard\ControllerInterface;
use Ep\Standard\ResponseHandlerInterface;

abstract class Controller implements ControllerInterface, ContextInterface
{
    private ?ViewInterface $view = null;

    public function run(string $actionName, $request): ?ResponseHandlerInterface
    {
        if (!is_callable([$this, $actionName])) {
            throw new RuntimeException(sprintf('%s::%s() is not found.', get_class($this), $actionName));
        }
        if ($this->beforeAction()) {
            $responseHandler = $this->$actionName($request);
            $this->afterAction($responseHandler);
            return $responseHandler;
        } else {
            return $this->createResponseHandler();
        }
    }

    protected function getView(): ViewInterface
    {
        if ($this->view === null) {
            $this->view = new View($this, Ep::getConfig()->viewPath);
        }
        return $this->view;
    }

    protected function string($string): string
    {
        return $string;
    }

    protected function render(string $view, array $params = []): ViewInterface
    {
        return $this->getView()->render($view, $params);
    }

    protected function renderPartial(string $view, array $params = []): ViewInterface
    {
        return $this->getView()->renderPartial($view, $params);
    }

    protected abstract function createResponseHandler(): ResponseHandlerInterface;

    protected abstract function beforeAction(): bool;

    protected abstract function afterAction(?ResponseHandlerInterface $response): void;
}
