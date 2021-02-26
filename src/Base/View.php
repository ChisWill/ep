<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Contract\ContextInterface;
use Ep\Helper\Alias;

class View
{
    public string $layout = 'main';

    private string $viewPath;
    private string $layoutDir;
    protected ?ContextInterface $context = null;
    private string $contextId;

    /**
     * @param ContextInterface|string $context
     */
    public function __construct(string $viewPath, $context)
    {
        $this->viewPath = $viewPath;
        $this->layoutDir = Ep::getConfig()->layoutDir;
        if ($context instanceof ContextInterface) {
            $this->context = $context;
            $this->contextId = $context->id;
        } else {
            $this->contextId = $context;
        }
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
            $path = '/' . $this->contextId . '/' . $path;
        }
        return $this->renderPhpFile($this->findViewFile($path), $params);
    }

    protected function loadFile(string $file): string
    {
        if (strpos($file, '/') !== 0) {
            $file = '/' . $this->contextId . '/' . $file;
        }
        return file_get_contents($this->findViewFile($file, false));
    }

    private function renderLayout(string $layout, array $params = []): string
    {
        if (strpos($layout, '/') !== 0) {
            $pos = strrpos($this->contextId, '/');
            if ($pos === false) {
                $layout = '/' . $this->layoutDir . '/' . $layout;
            } else {
                $layout = '/' . substr($this->contextId, 0, $pos) . '/' . $this->layoutDir . '/' . $layout;
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
