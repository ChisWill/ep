<?php

namespace Ep\Base;

use Ep;
use Ep\Helper\Alias;

class View implements ResponseHandlerInterface
{
    public string $title = '';
    public string $layout = 'layouts/main';

    private string $content;

    private Controller $controller;
    private string $viewFilePath;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;

        $this->initViewFilePath();
    }

    private function initViewFilePath()
    {
        $viewFilePath = strtr(Ep::getConfig()->viewFilePath, Ep::getDi()->get(Route::class)->getCapture());
        $viewFilePath = preg_replace('#<\w*>#', '', $viewFilePath);
        $this->viewFilePath = str_replace('//', '/', $viewFilePath);
    }

    public function send(): void
    {
        echo $this->content;
    }

    public function render(string $view, array $params = []): ResponseHandlerInterface
    {
        $this->content = $this->renderLayoutFile($view, $params);
        return $this;
    }

    public function renderPartial(string $view, array $params = []): ResponseHandlerInterface
    {
        $this->content = $this->renderContentFile($view, $params);
        return $this;
    }

    protected function renderLayoutFile(string $view, array $params = []): string
    {
        return $this->renderContentFile($this->layout, [
            'content' => $this->renderContentFile($view, $params)
        ]);
    }

    protected function renderContentFile(string $view, array $params = []): string
    {
        return $this->renderPhpFile($this->findViewFile($view), $params);
    }

    protected function findViewFile(string $view): string
    {
        return Alias::get($this->viewFilePath . '/' . $view . '.php');
    }

    protected function renderPhpFile(string $_file_, array $_params_ = []): string
    {
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        require($_file_);

        return ob_get_clean();
    }
}
