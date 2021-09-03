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
    private array $views = [];

    public function getView(): View
    {
        if ($this->view === null) {
            $this->view = Ep::getDi()
                ->get($this->getViewClass())
                ->withViewPath($this->getViewPath())
                ->withContext($this);
        }
        if (isset($this->actionId)) {
            $this->views[$this->actionId] ??= clone $this->view;
            return $this->views[$this->actionId];
        } else {
            return $this->view;
        }
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
