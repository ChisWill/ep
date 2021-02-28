<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ModuleInterface
{
    /**
     * @param mixed $request
     */
    public function bootstrap($request): void;
}
