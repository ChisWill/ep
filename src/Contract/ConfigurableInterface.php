<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ConfigurableInterface
{
    /**
     * 当不方便使用构造方法设置依赖参数时，可自定义参数，具体使用场景如下：
     * 1. 使用 DI 获取接口，且依赖参数不为类或接口，且依赖参数值随场景变化时
     * 2. 使用 DI 获取类，且不希望使用构造方法设置依赖时
     * 
     * @return static
     */
    public function setConfig(array $config);

    /**
     * 当想以某个实例为模板，快速复制一个具有新配置的实例时使用
     * 
     * @return static
     */
    public function withConfig(array $config);
}
