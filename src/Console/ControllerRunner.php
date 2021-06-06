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
    protected function runAction(ControllerInterface $command, string $action, $request): int
    {
        $this->symfonyApplication->add($this->wrapCommand($command, $action, $request));

        return $this->symfonyApplication->run($this->input, $this->output);
    }

    private function wrapCommand(ControllerInterface $command, string $action, ConsoleRequestInterface $request): SymfonyCommand
    {
        return new class($command, $request, fn () => parent::runAction($command, $action, $request)) extends SymfonyCommand
        {
            private ControllerInterface $command;
            private Closure $callback;

            public function __construct(ControllerInterface $command, ConsoleRequestInterface $request, Closure $callback)
            {
                $this->command = $command;
                $this->callback = $callback;

                parent::__construct($request->getRoute());
            }

            protected function configure(): void
            {
                if (method_exists($this->command, 'getDefinitions')) {
                    /** @var CommandDefinition[] $definitions */
                    $definitions = $this->command->getDefinitions();
                    if (array_key_exists($this->command->actionId, $definitions)) {
                        $this
                            ->setDefinition($definitions[$this->command->actionId]->getDefinition())
                            ->setDescription($definitions[$this->command->actionId]->getDescription())
                            ->setHelp($definitions[$this->command->actionId]->getHelp());
                    }
                }
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return call_user_func($this->callback);
            }
        };
    }
}
