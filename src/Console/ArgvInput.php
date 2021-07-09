<?php

declare(strict_types=1);

namespace Ep\Console;

use Symfony\Component\Console\Input\ArgvInput as SymfonyArgvInput;

final class ArgvInput extends SymfonyArgvInput
{
    private array $argvs = [];

    /**
     * {@inheritDoc}
     */
    public function hasArgument($name): bool
    {
        return array_key_exists($name, $this->argvs) || parent::hasArgument($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getArgument(string $name)
    {
        return $this->argvs[$name] ?? parent::getArgument($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setArgument(string $name, $value): void
    {
        $this->argvs[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getArguments(): array
    {
        return $this->argvs + parent::getArguments();
    }

    private array $opts = [];

    /**
     * {@inheritDoc}
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->opts) || parent::hasOption($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(string $name)
    {
        return $this->opts[$name] ?? parent::getOption($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setOption(string $name, $value): void
    {
        $this->opts[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): array
    {
        return $this->opts + parent::getOptions();
    }
}
