<?php

declare(strict_types=1);

namespace Ep\Contract;

interface AspectInterface
{
    /**
     * @return mixed
     */
    public function process(HandlerInterface $handler);
}
