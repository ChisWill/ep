<?php

declare(strict_types=1);

namespace Ep\Web\Event;

use Ep\Web\View;

abstract class ViewEvent
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function getView(): View
    {
        return $this->view;
    }
}
