<?php

declare(strict_types=1);

namespace Ep\Standard;

interface ControllerInterface
{
    public function run(string $action, $request);
}
