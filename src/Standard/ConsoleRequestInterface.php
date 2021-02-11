<?php

declare(strict_types=1);

namespace Ep\Standard;

interface ConsoleRequestInterface
{
    public function getRoute(): string;

    public function getParams(): array;
}
