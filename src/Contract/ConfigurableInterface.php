<?php

declare(strict_types=1);

namespace Ep\Contract;

use LogicException;

interface ConfigurableInterface
{
    /**
     * @return static
     * @throws LogicException
     */
    public function configure(array $config);

    /**
     * @return static
     * @throws LogicException
     */
    public function clone(array $config);
}
