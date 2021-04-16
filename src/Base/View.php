<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\ConfigurableInterface;
use Ep\Contract\ConfigurableTrait;
use Ep\Contract\ContextInterface;
use Yiisoft\Aliases\Aliases;

class View implements ConfigurableInterface
{
    use ConfigurableTrait;

    public string $layout = 'main';

    protected ?string $viewPath = null;
    protected ?ContextInterface $context = null;

    private Aliases $aliases;
    private Config $config;

    public function __construct(Config $config, Aliases $aliases)
    {
        $this->aliases = $aliases;
        $this->config = $config;
    }

    public function render(string $path, array $params = []): string
    {
        return $this->renderLayout($this->layout, [
            'content' => $this->renderPartial($path, $params)
        ]);
    }

    public function renderPartial(string $path, array $params = []): string
    {
        return $this->renderPhpFile($this->findViewFile($this->getFilePath($path)), $params);
    }

    public function renderFile(string $file): string
    {
        return file_get_contents($this->findViewFile($this->getFilePath($file), ''));
    }

    private function getViewPath(): string
    {
        if ($this->viewPath === null) {
            $this->viewPath = $this->config->viewPath;
        }
        return $this->viewPath;
    }

    private ?string $contextId = null;

    private function getContextId(): ?string
    {
        if ($this->contextId === null) {
            if ($this->context instanceof ContextInterface) {
                $this->contextId = $this->context->id;
            }
            if (is_string($this->contextId)) {
                $this->contextId = trim($this->contextId, '/');
            }
        }
        return $this->contextId;
    }

    private function getFilePath(string $path): string
    {
        $contextId = $this->getContextId();
        if ($contextId !== null && strpos($path, '/') !== 0) {
            $path = '/' . $contextId . '/' . $path;
        }
        return $path;
    }

    private function renderLayout(string $layout, array $params = []): string
    {
        if (strpos($layout, '/') !== 0) {
            $contextId = $this->getContextId();
            if ($contextId === null || ($pos = strrpos($contextId, '/')) === false) {
                $layout = '/' . $this->config->layoutDir . '/' . $layout;
            } else {
                $layout = '/' . substr($contextId, 0, $pos) . '/' . $this->config->layoutDir . '/' . $layout;
            }
        }
        return $this->renderPhpFile($this->findViewFile($layout), $params);
    }

    private function findViewFile(string $path, string $ext = '.php'): string
    {
        return $this->aliases->get($this->getViewPath() . $path . $ext);
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
