<?php

declare(strict_types=1);

namespace Ep\Base;

trait ContextTrait
{
    private ?View $view = null;

    public function getView(): View
    {
        if ($this->view === null) {
            $this->view = new View($this->getViewPath(), $this);
        }
        return $this->view;
    }

    abstract public function getViewPath(): string;
}
