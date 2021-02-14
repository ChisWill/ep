<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\View;
use Ep\Standard\ConsoleRequestInterface;
use Ep\Standard\ViewInterface;

abstract class Command extends \Ep\Base\Controller
{
    /**
     * @param ConsoleRequestInterface $request
     */
    protected function beforeAction($request): bool
    {
        return true;
    }

    /**
     * @param  ConsoleRequestInterface $request 
     * @param  string                  $response
     * 
     * @return string
     */
    protected function afterAction($request, $response)
    {
        return $response;
    }

    private ?ViewInterface $view = null;

    /**
     * @return View
     */
    protected function getView(): ViewInterface
    {
        if ($this->view === null) {
            $this->view = Ep::getInjector()->make(View::class, ['context' => $this, 'viewPath' => Ep::getConfig()->viewPath]);
        }
        return $this->view;
    }
}
