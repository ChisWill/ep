<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep;
use InvalidArgumentException;

trait ConfigurableTrait
{
    /**
     * @return static
     */
    public function configure(array $config)
    {
        if (Ep::getConfig()->debug) {
            foreach ($config as $k => $v) {
                if (property_exists($this, $k)) {
                    $this->$k = $v;
                } else {
                    throw new InvalidArgumentException("The \"{$k}\" configuration is not exists.");
                }
            }
        } else {
            foreach ($config as $k => $v) {
                $this->$k = $v;
            }
        }

        return $this;
    }

    /**
     * @return static
     */
    public function clone(array $config)
    {
        $new = clone $this;
        return $new->configure($config);
    }
}
