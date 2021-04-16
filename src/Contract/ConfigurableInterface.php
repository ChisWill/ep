<?php

declare(strict_types=1);

namespace Ep\Contract;

use LogicException;

interface ConfigurableInterface
{
    /**
     * 设置属性，该属性必须已定义，否则抛出异常
     * 
     * @return static
     * @throws LogicException
     */
    public function configure(array $config);

    /**
     * 克隆并设置属性
     * 
     * @return static
     * @throws LogicException
     */
    public function clone(array $config);
}
