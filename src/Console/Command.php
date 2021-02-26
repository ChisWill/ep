<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\View;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ControllerInterface;

abstract class Command implements ControllerInterface
{
    /**
     * @param  ConsoleRequestInterface $request
     * 
     * @return mixed
     */
    public function before($request)
    {
        return true;
    }

    /**
     * @param  ConsoleRequestInterface $request 
     * @param  mixed                   $response
     * 
     * @return mixed
     */
    public function after($request, $response)
    {
        return $response;
    }

    private ?View $view = null;

    /**
     * @return View
     */
    protected function getView(): View
    {
        if ($this->view === null) {
            $this->view = new View(Ep::getConfig()->viewPath, $this);
        }
        return $this->view;
    }
}
