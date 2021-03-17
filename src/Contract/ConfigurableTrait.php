<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep;
use InvalidArgumentException;
use LogicException;

trait ConfigurableTrait
{
    private bool $isConfigured = false;

    /**
     * {@inheritDoc}
     */
    public function configure(array $config)
    {
        if ($this->isConfigured === true) {
            throw new LogicException(static::class . ' had been configured.');
        }
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
        $this->isConfigured = true;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clone(array $config)
    {
        $new = clone $this;
        return $new->configure($config);
    }
}
