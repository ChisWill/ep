<?php

declare(strict_types=1);

namespace Ep\Command;

use InvalidArgumentException;

class Service
{
    /**
     * @throws InvalidArgumentException
     */
    protected function required(string $option)
    {
        throw new InvalidArgumentException("The \"{$option}\" option is required.");
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function invalid(string $option, string $value)
    {
        throw new InvalidArgumentException("The value \"{$value}\" of the option \"{$option}\" is invalid.");
    }
}
