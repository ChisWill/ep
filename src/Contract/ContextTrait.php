<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep;
use Ep\Base\View;

trait ContextTrait
{
    private ?View $view = null;

    public function getView(): View
    {
        if ($this->view === null) {
            $this->view = Ep::getDi()->get(View::class)->clone([
                'viewPath' => $this->getViewPath(),
                'context' => $this
            ]);
        }
        return $this->view;
    }

    public function getViewPath(): string
    {
        return Ep::getConfig()->viewPath;
    }
}
