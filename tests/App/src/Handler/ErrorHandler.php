<?php

declare(strict_types=1);

namespace Ep\Tests\App\Handler;

use Ep;
use Ep\Base\ContextTrait;
use Ep\Contract\ErrorHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
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
