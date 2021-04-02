<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use Ep\Contract\ContextInterface;
use Yiisoft\Aliases\Aliases;

class View
{
    public string $layout = 'main';

    private Aliases $aliases;
    private string $viewPath;
    protected ?ContextInterface $context = null;
    private string $contextId;
    private string $layoutDir;

    /**
     * @param ContextInterface|string $context
     */
    public function __construct(string $viewPath, $context)
    {
        $this->aliases = Ep::getDi()->get(Aliases::class);
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
        return $this->aliases->get($this->viewPath . $path . ($isPHPFile ? '.php' : ''));
    }

    /**
     * @param string $file
     * @param array  $params
     */
    private function renderPhpFile(): string
    {
        ob_start();
        PHP_VERSION_ID >= 80000 ? ob_implicit_flush(false) : ob_implicit_flush(0);
        extract(func_get_arg(1), EXTR_OVERWRITE);
        require(func_get_arg(0));

        return ob_get_clean();
    }
}
