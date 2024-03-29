<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleRequestInterface;
use Symfony\Component\Console\Input\InputInterface;
use Yiisoft\Arrays\ArrayHelper;

final class Request implements ConsoleRequestInterface
{
    private InputInterface $input;

    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(): string
    {
        return $this->input->getFirstArgument() ?? '';
    }

    private array $arguments = [];

    /**
     * {@inheritDoc}
     */
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments) || $this->input->hasArgument($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getArgument(string $name)
    {
        if (strpos($name, '.') !== false) {
            return ArrayHelper::getValueByPath($this->getArguments(), $name);
        }
        return $this->arguments[$name] ?? $this->input->getArgument($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setArgument(string $name, $value): void
    {
        $this->arguments[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getArguments(): array
    {
        return $this->arguments + $this->input->getArguments();
    }

    /**
     * {@inheritDoc}
     */
    public function setArguments(array $arguments): void
    {
        foreach ($arguments as $name => $value) {
            $this->setArgument($name, $value);
        }
    }

    private array $options = [];

    /**
     * {@inheritDoc}
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options) || $this->input->hasOption($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getOption(string $name)
    {
        if (strpos($name, '.') !== false) {
            return ArrayHelper::getValueByPath($this->getOptions(), $name);
        }
        return $this->options[$name] ?? $this->input->getOption($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setOption(string $name, $value): void
    {
        $this->options[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): array
    {
        return $this->options + $this->input->getOptions();
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }
}
