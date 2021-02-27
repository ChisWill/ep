<?php

declare(strict_types=1);

namespace Ep\Contract;

use Throwable;

interface ErrorRendererInterface
{
    public function render(Throwable $t, $request = null): string;

    public function log(Throwable $t, $request = null): void;
}
