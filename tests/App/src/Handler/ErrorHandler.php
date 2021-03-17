<?php

declare(strict_types=1);

namespace Ep\Tests\App\Handler;

use Ep;
use Ep\Contract\ContextTrait;
use Ep\Contract\WebErrorHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorHandler implements WebErrorHandlerInterface
{
    use ContextTrait;

    public string $id = 'error';

    public function getViewPath(): string
    {
        return Ep::getConfig()->viewPath;
    }

    public function render(Throwable $t, ServerRequestInterface $request): string
    {
        return $this->getView()->renderPartial('demo', compact('t', 'request'));
    }
}
