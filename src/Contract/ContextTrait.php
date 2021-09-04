<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep;
use Ep\Base\View;

/**
 * @property string $id
 */
trait ContextTrait
{
    private ?View $view = null;

    public function getView(): View
    {
        if ($this->view === null) {
            $this->view = $this->createView();
        }
        return $this->view;
    }

    private function createView(): View
    {
        return Ep::getInjector()
            ->make($this->getViewClass())
            ->withViewPath($this->getViewPath())
            ->withContext($this);
    }

    protected function getViewClass(): string
    {
        return View::class;
    }

    protected function getViewPath(): string
    {
        return Ep::getConfig()->viewPath;
    }
}
