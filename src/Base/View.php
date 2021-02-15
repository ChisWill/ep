<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Contract\ContextInterface;
use Ep\Helper\Alias;

class View
{
    public string $layout = 'main';

    protected Config $config;
    protected ContextInterface $context;

    private string $viewPath;

    public function __construct(ContextInterface $context, string $viewPath)
    {
        $this->config = Ep::getConfig();
        $this->context = $context;
        $this->viewPath = $viewPath;
    }

    public function render(string $path, array $params = []): string
    {
        return $this->renderLayout($this->layout, [
            'content' => $this->renderPartial($path, $params)
        ]);
    }

    public function renderPartial(string $path, array $params = []): string
    {
        if (strpos($path, '/') !== 0) {
            $path = '/' . $this->context->id . '/' . $path;
        }
        return $this->renderPhpFile($this->findViewFile($path), $params);
    }

    protected function loadFile(string $file): string
    {
        if (strpos($file, '/') !== 0) {
            $file = '/' . $this->context->id . '/' . $file;
        }
        return file_get_contents($this->findViewFile($file, false));
    }

    private function renderLayout(string $layout, array $params = []): string
    {
        if (strpos($layout, '/') !== 0) {
            $id = $this->context->id;
            $pos = strrpos($id, '/');
            if ($pos === false) {
                $layout = '/' . $this->config->layoutDir . '/' . $layout;
            } else {
                $layout = '/' . substr($id, 0, $pos) . '/' . $this->config->layoutDir . '/' . $layout;
            }
        }
        return $this->renderPhpFile($this->findViewFile($layout), $params);
    }

    private function findViewFile(string $path, bool $isPHPFile = true): string
    {
        return Alias::get($this->viewPath . $path . ($isPHPFile ? '.php' : ''));
    }

    private function renderPhpFile(string $_file_, array $_params_ = []): string
    {
        ob_start();
        ob_implicit_flush(0);
        extract($_params_, EXTR_OVERWRITE);
        require($_file_);

        return ob_get_clean();
    }
}
