<?php

declare(strict_types=1);

namespace Ep\Contract;

use InvalidArgumentException;

interface ConfigurableInterface
{
    /**
     * @return static
     * @throws InvalidArgumentException
     */
    public function configure(array $properties);

    /**
     * @return static
     * @throws InvalidArgumentException
     */
    public function clone(array $properties);
}
