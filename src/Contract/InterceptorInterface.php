<?php

declare(strict_types=1);

namespace Ep\Contract;

interface InterceptorInterface
{
    public function includePath(): array;

    public function excludePath(): array;
}
