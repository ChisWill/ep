<?php

namespace ep\base;

use ep\Core;

class View
{
    public $title = '';

    public $context;

    public function render($view, $params = [])
    {
        return $this->renderPhpFile($this->findViewFile($view), $params);
    }

    public function findViewFile($view)
    {
        return Core::getAlias(Core::$config->viewFilePath) . '/' . $view . '.php';
    }

    public function renderPhpFile($_file_, $_params_ = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        require($_file_);

        return ob_get_clean();
    }
}
