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

    public function withOutput(OutputInterface $output): self
    {
        $new = clone $this;
        $new->output = $output;
        return $new;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    private int $code = Command::OK;

    public function withCode(int $code): ConsoleResponseInterface
    {
        $new = clone $this;
        $new->code = $code;
        return $new;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, int $level = 0): void
    {
        $this->output->write($messages, false, $level);
    }

    /**
     * {@inheritDoc}
     */
    public function writeln($messages, int $level = 0): void
    {
        $this->output->write($messages, true, $level);
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
