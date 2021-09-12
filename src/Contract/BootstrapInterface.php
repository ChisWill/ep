<?php

declare(strict_types=1);

namespace Ep\Contract;

interface BootstrapInterface
{
    public function bootstrap(array $data = []): void;
}
