<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleResponse implements ConsoleResponseInterface
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, int $options = 0, bool $newline = false): void
    {
        $this->output->write($messages, $newline, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function writeln($messages, int $options = 0): void
    {
        $this->output->writeln($messages, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function setVerbosity(int $level): void
    {
        $this->output->setVerbosity($level);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosity(): int
    {
        return $this->output->getVerbosity();
    }

    /**
     * {@inheritDoc}
     */
    public function isQuiet(): bool
    {
        return $this->output->isQuiet();
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose(): bool
    {
        return $this->output->isVerbose();
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose(): bool
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug(): bool
    {
        return $this->output->isDebug();
    }
}
