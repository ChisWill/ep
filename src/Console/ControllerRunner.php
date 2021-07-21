<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\ControllerRunner as BaseControllerRunner;

final class ControllerRunner extends BaseControllerRunner
{
    /**
     * {@inheritDoc}
     */
    public function getControllerSuffix(): string
    {
        return $this->config->commandSuffix;
    }
}
