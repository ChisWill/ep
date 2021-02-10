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
    public string $layout = 'layouts/main';

    private string $viewPath;
    private ContextInterface $context;

    public function __construct(ContextInterface $context, string $viewPath)
    {
        $this->context = $context;
        $this->viewPath = rtrim(preg_replace('#/+#', '/', preg_replace('#<\w*>#', '', strtr($viewPath, Ep::getDi()->get(RouteInterface::class)->getCaptureParams()))), '/');
    }

    public function render(string $path, array $params = []): string
    {
        return $this->renderPartial($this->layout, [
            'content' => $this->renderPartial($path, $params)
        ]);
    }

    public function renderPartial(string $path, array $params = []): string
    {
        return $this->renderPhpFile($this->findViewFile($path), $params);
    }

    protected function findViewFile(string $path): string
    {
        if (strpos($path, '/') === false) {
            $path = $this->context->getId() . '/' . $path;
        }
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
