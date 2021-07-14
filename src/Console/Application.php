<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ErrorHandler;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Application extends SymfonyApplication
{
    private InputInterface $input;
    private OutputInterface $output;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->errorHandler = $errorHandler;
        $this->errorRenderer = $errorRenderer;

        parent::__construct('Ep', Ep::VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function run(?InputInterface $input = null, ?OutputInterface $output = null)
    {
        $input ??= $this->input;
        $output ??= $this->output;

        $this->registerEvent($input);

        return parent::run($input, $output);
    }

    public function registerEvent(InputInterface $input): void
    {
        $this->errorHandler
            ->configure([
                'errorRenderer' => $this->errorRenderer
            ])
            ->register($input);
    }

    /**
     * {@inheritDoc}
     */
    public function extractNamespace(string $name, int $limit = null)
    {
        $parts = explode('/', $name, -1);

        return ucfirst(implode('/', null === $limit ? $parts : array_slice($parts, 0, $limit)));
    }
}
