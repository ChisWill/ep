<?php

declare(strict_types=1);

namespace Ep\Contract;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ConsoleFactoryInterface
{
    public function createRequest(InputInterface $input = null): ConsoleRequestInterface;

    public function createResponse(OutputInterface $output = null): ConsoleResponseInterface;
}
