<?php

declare(strict_types=1);

namespace Ep\Contract;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface WebErrorRendererInterface extends ContextInterface
{
    public function render(Throwable $t, ServerRequestInterface $request): string;
}
