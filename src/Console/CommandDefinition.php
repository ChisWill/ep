<?php

declare(strict_types=1);

namespace Ep\Console;

final class CommandDefinition
{
    private string $action;
    private array $definition;

    public function __construct(string $action, array $definition = [])
    {
        $this->action = $action;
        $this->definition = $definition;
    }

    public function getDefinition(): array
    {
        return $this->definition;
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
