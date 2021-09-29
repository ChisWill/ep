<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ContextInterface;
use Yiisoft\Aliases\Aliases;
use Psr\Container\ContainerInterface;

class View
{
    protected Config $config;
    protected Aliases $aliases;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(Config::class);
        $this->aliases = $container->get(Aliases::class);
    }

    private string $layout = 'main';

    public function withLayout(string $layout): self
    {
        $new = clone $this;
        $new->layout = $layout;
        return $new;
    }

    private ?string $viewPath = null;

    public function withViewPath(string $viewPath): self
    {
        $new = clone $this;
        $new->viewPath = $viewPath;
        return $new;
    }

    private ?ContextInterface $context = null;

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
        return $this->renderPHPFile($this->findFilePath($this->normalize($path)), $params);
    }

    public function renderFile(string $file): string
    {
        return file_get_contents($this->findFilePath($this->normalize($file), ''));
    }

    private function normalize(string $path): string
    {
        if ($this->prefix !== null && strpos($path, '/') !== 0) {
            $path = sprintf('/%s/%s', $this->prefix, $path);
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
                $layout = sprintf('/%s/%s', $this->config->layoutDir, $layout);
            } else {
                $layout = sprintf('/%s/%s/%s', substr($this->prefix, 0, $pos), $this->config->layoutDir, $layout);
            }
        }
        return $this->renderPHPFile($this->findFilePath($layout), $params);
    }

    private function findFilePath(string $view, string $ext = '.php'): string
    {
        return $this->aliases->get($this->getViewPath() . $view . $ext);
    }

    private function renderPHPFile(): string
    {
        ob_start();
        PHP_VERSION_ID >= 80000 ? ob_implicit_flush(false) : ob_implicit_flush(0);
        extract(func_get_arg(1), EXTR_OVERWRITE);
        require(func_get_arg(0));

        return ob_get_clean();
    }
}
