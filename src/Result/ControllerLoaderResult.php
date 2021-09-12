<?php

declare(strict_types=1);

namespace Ep\Result;

use Ep\Contract\ControllerInterface;
use Ep\Contract\ModuleInterface;

final class ControllerLoaderResult
{
    private ?ModuleInterface $module;
    private ControllerInterface $controller;
    private string $action;

    public function __construct(?ModuleInterface $module, ControllerInterface $controller, string $action)
    {
        $this->module = $module;
        $this->controller = $controller;
        $this->action = $action;
    }

    public function getModule(): ?ModuleInterface
    {
        return $this->module;
    }

    public function getController(): ControllerInterface
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
