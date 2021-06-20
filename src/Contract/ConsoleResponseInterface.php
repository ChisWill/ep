<?php

declare(strict_types=1);

namespace Ep\Contract;

use Symfony\Component\Console\Output\OutputInterface;

interface ConsoleResponseInterface
{
    public function withOutput(OutputInterface $output): self;

    public function getOutput(): OutputInterface;

    public function withCode(int $code): self;

    public function getCode(): int;

    /**
     * @param string|iterable $messages
     */
    public function write($messages, int $level = 0): void;

    /**
     * @param string|iterable $messages
     */
    public function writeln($messages, int $level = 0): void;

    public function setVerbosity(int $level): void;

    public function getVerbosity(): int;

    public function isQuiet(): bool;

    public function isVerbose(): bool;

    public function isVeryVerbose(): bool;

    public function isDebug(): bool;
}
