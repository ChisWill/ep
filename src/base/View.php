<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Helper\Alias;
use Ep\Standard\ViewInterface;
use Ep\Standard\RouteInterface;
use Ep\Standard\ContextInterface;

class View implements ViewInterface
{
    public string $title = '';
    public string $layout = 'layouts/main';

    private string $content;
    private string $viewPath;
    private ContextInterface $context;

    public function __construct(ContextInterface $context, string $viewPath)
    {
        $this->context = $context;
        $this->viewPath = str_replace('//', '/', preg_replace('#<\w*>#', '', strtr($viewPath, Ep::getDi()->get(RouteInterface::class)->getCapture())));
    }

    public function send(): void
    {
        echo $this->content;
    }

    public function render(string $path, array $params = []): ViewInterface
    {
        $this->content = $this->renderLayoutFile($path, $params);
        return $this;
    }

    public function renderPartial(string $path, array $params = []): ViewInterface
    {
        $this->content = $this->renderContentFile($path, $params);
        return $this;
    }

    protected function renderLayoutFile(string $path, array $params = []): string
    {
        return $this->renderContentFile($this->layout, [
            'content' => $this->renderContentFile($path, $params)
        ]);
    }

    protected function renderContentFile(string $path, array $params = []): string
    {
        return $this->renderPhpFile($this->findViewFile($path), $params);
    }

    protected function findViewFile(string $path): string
    {
        return Alias::get($this->viewPath . '/' . $path . '.php');
    }

    protected function renderPhpFile(string $_file_, array $_params_ = []): string
    {
        ob_start();
        ob_implicit_flush(0);
        extract($_params_, EXTR_OVERWRITE);
        require($_file_);

        return ob_get_clean();
    }
}
