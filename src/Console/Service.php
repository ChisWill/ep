<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleResponseInterface;

final class Service
{
    private ConsoleResponseInterface $consoleResponse;

    public function __construct(ConsoleResponseInterface $consoleResponse)
    {
        $this->consoleResponse = $consoleResponse;
    }

    public function getOutput(): ConsoleResponseInterface
    {
        return $this->consoleResponse;
    }

    public function write(string $message, int $options = 0): void
    {
        $this->consoleResponse->write($message, $options);
    }

    public function writeln(string $message, int $options = 0): void
    {
        $this->consoleResponse->writeln($message, $options);
    }

    public function prompt(string $message): string
    {
        fwrite(STDOUT, $message);
        return rtrim(fgets(STDIN), PHP_EOL);
    }
}
