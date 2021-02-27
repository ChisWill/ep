<?php

declare(strict_types=1);

namespace Ep\Contract;

use HttpSoft\Message\ServerRequest;
use Throwable;

interface ErrorHandlerInterface extends ContextInterface
{
    public function render(Throwable $t, ServerRequest $request): string;
}
