<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ErrorHandler;
use Ep\Contract\ConsoleFactoryInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Application extends SymfonyApplication
{
    private InputInterface $input;
    private OutputInterface $output;
    private ConsoleFactoryInterface $factory;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        ConsoleFactoryInterface $factory,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->factory = $factory;
        $this->errorHandler = $errorHandler;
        $this->errorRenderer = $errorRenderer;

        parent::__construct('Ep', Ep::VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $input ??= $this->input;
        $output ??= $this->output;

        $this->errorHandler->register(
            $this->factory->createRequest($input),
            $this->errorRenderer
        );

        return parent::run($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    public function extractNamespace(string $name, int $limit = null)
    {
        $parts = explode('/', $name, -1);

        return ucfirst(implode('/', $limit === null ? $parts : array_slice($parts, 0, $limit)));
    }
}
