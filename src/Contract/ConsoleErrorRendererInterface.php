<?php

declare(strict_types=1);

namespace Ep\Contract;

use Throwable;

interface ConsoleErrorRendererInterface
{
    public function render(Throwable $t, ConsoleRequestInterface $request): string;

    public function log(Throwable $t, ConsoleRequestInterface $request): void;
}
