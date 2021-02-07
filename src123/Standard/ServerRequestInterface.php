<?php

declare(strict_types=1);

namespace Ep\Standard;

interface ServerRequestInterface extends \Psr\Http\Message\ServerRequestInterface
{
    public function isAjax(): bool;
}
