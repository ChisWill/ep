<?php

namespace Ep\Console;

use Ep;
use Ep\Base\View;
use Ep\Standard\ViewInterface;

class Controller extends \Ep\Base\Controller
{
    public function __construct()
    {
    }

    protected function beforeAction($request): bool
    {
        return true;
    }

    protected function afterAction($response)
    {
        return $response;
    }

    private ?ViewInterface $view = null;

    /**
     * @inheritDoc
     */
    protected function getView(): ViewInterface
    {
        if ($this->view === null) {
            $this->view = Ep::getInjector()->make(View::class, ['context' => $this, 'viewPath' => Ep::getConfig()->viewPath]);
        }
        return $this->view;
    }
}
