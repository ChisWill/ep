<?php

declare(strict_types=1);

namespace Ep\Contract;

interface ConsoleResponseInterface
{
    /**
     * @param string|iterable $messages
     */
    public function write($messages, int $options = 0, bool $newline = false): void;

    /**
     * @param string|iterable $messages
     */
    public function writeln($messages, int $options = 0): void;

    public function setVerbosity(int $level): void;

    public function getVerbosity(): int;

    public function isQuiet(): bool;

    public function isVerbose(): bool;

    public function isVeryVerbose(): bool;

    public function isDebug(): bool;
}
