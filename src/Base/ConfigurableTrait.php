<?php

declare(strict_types=1);

namespace Ep\Base;

trait ConfigurableTrait
{
    /**
     * @return static
     */
    public function setConfig(array $config)
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
        return $this;
    }

    /**
     * @return static
     */
    public function withConfig(array $config)
    {
        $new = clone $this;
        foreach ($config as $k => $v) {
            $new->$k = $v;
        }
        return $new;
    }
}
