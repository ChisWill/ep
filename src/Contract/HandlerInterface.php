<?php

declare(strict_types=1);

namespace Ep\Contract;

interface HandlerInterface
{
    /**
     * @return mixed
     */
    public function handle();
}
