<?php

declare(strict_types=1);

namespace Ep\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class CommandDefinition
{
    private array $definitions = [];

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param mixed $default
     */
    public function addArgument(string $name, int $mode = null, string $description = '', $default = null): self
    {
        $this->definitions[] = new InputArgument($name, $mode, $description, $default);
        return $this;
    }

    /**
     * @param mixed $default
     */
    public function addOption(string $name, ?string $shortcut = null, int $mode = null, string $description = '', $default = null): self
    {
        $this->definitions[] = new InputOption($name, $shortcut, $mode, $description, $default);
        return $this;
    }

    private string $description = '';

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    private array $usages = [];

    public function getUsages(): array
    {
        return $this->usages;
    }

    public function addUsage(string $usage): self
    {
        $this->usages[] = $usage;
        return $this;
    }

    private string $help = '';

    public function getHelp(): string
    {
        return $this->help;
    }

    public function setHelp(string $help): self
    {
        $this->help = $help;
        return $this;
    }
}
