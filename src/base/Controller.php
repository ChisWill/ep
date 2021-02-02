<?php

declare(strict_types=1);

namespace Ep\base;

use RuntimeException;

abstract class Controller
{
    private ?View $view = null;

    public function run(string $actionName, $request): ResponseHandlerInterface
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

    protected function getView(): View
    {
        if ($this->view === null) {
            $this->view = new View($this);
        }
        return $this->view;
    }

    protected function string($string): string
    {
        return $string;
    }

    protected function render(string $view, array $params = []): ResponseHandlerInterface
    {
        return $this->getView()->render($view, $params);
    }

    protected function renderPartial(string $view, array $params = []): ResponseHandlerInterface
    {
        return $this->getView()->renderPartial($view, $params);
    }

    protected abstract function createResponseHandler(): ResponseHandlerInterface;

    protected abstract function beforeAction(): bool;

    protected abstract function afterAction(ResponseHandlerInterface $response): void;
}
