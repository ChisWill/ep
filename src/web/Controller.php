<?php

namespace ep\web;

use ep\base\Controller as BaseController;

class Controller extends BaseController
{
    public View $view;

    protected function render($path = [], $params = [])
    {
        if (is_array($path)) {
            $params = $path;
            $caller = debug_backtrace()[1];
            $path = $this->getControllerName($caller['class']) . '/' . $caller['function'];
        }
        return $this->view->render($path, $params);
    }
}
