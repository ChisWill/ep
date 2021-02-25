<?php

declare(strict_types=1);

namespace Ep\Contract;

use LogicException;

interface ConfigurableInterface
{
    /**
     * 直接设置参数，并且只能调用一次
     * 
     * @return static
     * @throws LogicException
     */
    public function configure(array $config);

    /**
     * 克隆自身并设置参数，不能再次克隆
     * 
     * @return static
     * @throws LogicException
     */
    public function clone(array $config);
}
