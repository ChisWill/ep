<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ContextInterface;
use Yiisoft\Aliases\Aliases;

class View
{
    public string $layout = 'main';

    private Config $config;
    private Aliases $aliases;

    public function __construct(Config $config, Aliases $aliases)
    {
        $this->config = $config;
        $this->aliases = $aliases;
    }

    private ?string $viewPath = null;

    public function withViewPath(string $viewPath): self
    {
        $new = clone $this;
        $new->viewPath = $viewPath;
        return $new;
    }

    protected ?ContextInterface $context = null;

    public function withContext(ContextInterface $context): self
    {
        $new = clone $this;
        $new->context = $context;
        $new->prefix = $context->id;
        return $new;
    }

    private ?string $prefix = null;

    public function withPrefix(string $prefix): self
    {
        $new = clone $this;
        $new->prefix = $prefix;
        return $new;
    }

    public function render(string $path, array $params = []): string
    {
        return $this->renderLayout($this->layout, [
            'content' => $this->renderPartial($path, $params)
        ]);
    }

    public function renderPartial(string $path, array $params = []): string
    {
        return $this->renderPHPFile($this->findFilePath($this->normalizePath($path)), $params);
    }

    public function renderFile(string $file): string
    {
        return file_get_contents($this->findFilePath($this->normalizePath($file), ''));
    }

    private function normalizePath(string $path): string
    {
        if ($this->prefix !== null && strpos($path, '/') !== 0) {
            $path = '/' . $this->prefix . '/' . $path;
        }
        return $path;
    }

    private function getViewPath(): string
    {
        if ($this->viewPath === null) {
            $this->viewPath = $this->config->viewPath;
        }
        return $this->viewPath;
    }

    private function renderLayout(string $layout, array $params = []): string
    {
        if (strpos($layout, '/') !== 0) {
            if ($this->prefix === null || ($pos = strrpos($this->prefix, '/')) === false) {
                $layout = '/' . $this->config->layoutDir . '/' . $layout;
            } else {
                $layout = '/' . substr($this->prefix, 0, $pos) . '/' . $this->config->layoutDir . '/' . $layout;
            }
        }
        return $this->renderPHPFile($this->findFilePath($layout), $params);
    }

    private function findFilePath(string $view, string $ext = '.php'): string
    {
        return $this->aliases->get($this->getViewPath() . $view . $ext);
    }

    /**
     * @param string $file
     * @param array  $params
     */
    private function renderPHPFile(): string
    {
        ob_start();
        PHP_VERSION_ID >= 80000 ? ob_implicit_flush(false) : ob_implicit_flush(0);
        extract(func_get_arg(1), EXTR_OVERWRITE);
        require(func_get_arg(0));

        return ob_get_clean();
    }
}
