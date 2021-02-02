<?php

declare(strict_types=1);

namespace Ep\Base;

interface ResponseHandlerInterface
{
    public function send(): void;
}
