<?php

declare(strict_types=1);

namespace Ep\Standard;

interface ResponseHandlerInterface
{
    public function send(): void;
}
