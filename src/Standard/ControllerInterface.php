<?php

declare(strict_types=1);

namespace Ep\Standard;

interface ControllerInterface extends ConfigurableInterface
{
    public function run(string $action, $request);

    public function getSuffix(): string;
}
