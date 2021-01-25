<?php

namespace Ep\base;

use Ep\Exception;
use Ep\helper\Ep;

abstract class View
{
    public $title = '';
    public $layout = 'layouts/main';

    public function render(string $view, array $params = []): string
    {
        if ($this->layout === null) {
            return $this->renderContentFile($view, $params);
        } else {
            return $this->renderLayoutFile($view, $params);
        }
    }

    protected function renderContentFile(string $view, array $params = []): string
    {
        return $this->renderPhpFile($this->findViewFile($view), $params);
    }

    protected function renderLayoutFile(string $view, array $params = []): string
    {
        try {
            return $this->renderContentFile($this->layout, [
                'content' => $this->renderContentFile($view, $params)
            ]);
        } catch (Exception $e) {
            throw new Exception(Exception::NOT_FOUND_LAYOUT, $e->getMessage());
        }
    }

    protected function findViewFile(string $view): string
    {
        return Ep::getAlias($this->getViewFilePath() . '/' . $view . '.php');
    }

    protected function renderPhpFile(string $_file_, array $_params_ = []): string
    {
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        require($_file_);

        return ob_get_clean();
    }

    private function getViewFilePath()
    {
        return Ep::getConfig()->viewFilePath;
    }
}
