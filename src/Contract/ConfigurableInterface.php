<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ConfigurableInterface
{
    /**
     * 直接设置参数
     * 
     * @return static
     */
    public function configure(array $config);

    /**
     * 克隆自身并设置参数
     * 
     * @return static
     */
    public function clone(array $config);
}
