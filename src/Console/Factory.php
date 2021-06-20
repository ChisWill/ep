<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Factory
{
    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function createRequest(InputInterface $input = null): ConsoleRequestInterface
    {
        return new ConsoleRequest($input ?? $this->input);
    }

    public function createResponse(OutputInterface $output = null): ConsoleResponseInterface
    {
        return new ConsoleResponse($output ?? $this->output);
    }
}
