<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use Ep\Contract\ContextTrait;
use Ep\Contract\WebErrorRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorRenderer implements WebErrorRendererInterface
{
    use ContextTrait;

    public string $id = 'error';

    public function render(Throwable $t, ServerRequestInterface $request): string
    {
        return $this->getView()->renderPartial('demo', compact('t', 'request'));
    }
}
