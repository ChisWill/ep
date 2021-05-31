<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ControllerRunner as BaseControllerRunner;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ControllerInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use Closure;

final class ControllerRunner extends BaseControllerRunner
{
    private SymfonyApplication $symfonyApplication;
    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(
        ContainerInterface $container,
        SymfonyApplication $symfonyApplication,
        InputInterface $input,
        OutputInterface $output
    ) {
        parent::__construct($container);

        $this->symfonyApplication = $symfonyApplication;
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    protected function invoke(ControllerInterface $command, string $action, $request): int
    {
        $symfonyCommand = $this->wrapCommand($command, $action, $request);
        $this->symfonyApplication->add($symfonyCommand);

        return $this->symfonyApplication->run($this->input, $this->output);
    }

    private function wrapCommand(Command $command, string $action, ConsoleRequestInterface $request): SymfonyCommand
    {
        return new class($command, fn () => parent::invoke($command, $action, $request)) extends SymfonyCommand
        {
            private Command $command;
            private Closure $callback;

            public function __construct(Command $command, Closure $callback)
            {
                $this->command = $command;
                $this->callback = $callback;

                parent::__construct($command->actionId === Ep::getConfig()->defaultAction ? $command->id : ($command->id . '/' . $command->actionId));
            }

            protected function configure(): void
            {
                $definitionMethod = $this->command->actionId . 'Definition';
                if (method_exists($this->command, $definitionMethod)) {
                    $this->setDefinition(call_user_func([$this->command, $definitionMethod]));
                }
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return call_user_func($this->callback);
            }
        };
    }
}
