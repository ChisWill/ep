<?php

declare(strict_types=1);

namespace Ep\Contract;

use LogicException;

/**
 * 用于配置非必填的属性
 */
interface ConfigurableInterface
{
    /**
     * 配置属性，并且只能调用一次，该属性必须已定义，否则抛出异常
     * 
     * @return static
     * @throws LogicException
     */
    public function configure(array $config);

    /**
     * 克隆自身并配置属性，不能再次克隆，该属性必须已定义，否则抛出异常
     * 
     * @return static
     * @throws LogicException
     */
    public function clone(array $config);
}
