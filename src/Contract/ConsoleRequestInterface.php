<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ConsoleRequestInterface
{
    public function getRoute(): string;

    public function hasArgument(string $name): bool;

    /**
     * @return string|string[]|null
     */
    public function getArgument(string $name);

    /**
     * @param string|string[]|null $value
     */
    public function setArgument(string $name, $value): void;

    public function getArguments(): array;

    public function setArguments(array $arguments): void;

    public function hasOption(string $name): bool;

    /**
     * @return string|string[]|null
     */
    public function getOption(string $name);

    /**
     * @param string|string[]|null $value
     */
    public function setOption(string $name, $value): void;

    public function getOptions(): array;

    public function setOptions(array $options): void;

    /**
     * @param string|array $values
     */
    public function hasParameterOption($values): bool;
}
