<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\ControllerRunner as BaseControllerRunner;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Ep\Contract\ControllerInterface;
use Ep\Contract\ModuleInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Container\ContainerInterface;
use Closure;
use LogicException;

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

    public function withInput(InputInterface $input): self
    {
        $new = clone $this;
        $new->input = $input;
        return $new;
    }

    public function withOutput(OutputInterface $output): self
    {
        $new = clone $this;
        $new->output = $output;
        return $new;
    }

    protected function getControllerSuffix(): string
    {
        return $this->config->commandDirAndSuffix;
    }

    private bool $runned = false;

    /**
     * {@inheritDoc}
     */
    protected function runModule(ModuleInterface $module, ControllerInterface $command, string $action, $request)
    {
        $this->runned = true;
        return $this->runCommand(
            $this->wrapCommand($command, $request, fn () => parent::runModule($module, $command, $action, $request))
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function runAction(ControllerInterface $command, string $action, $request)
    {
        if ($this->runned) {
            $this->runned = false;
            return parent::runAction($command, $action, $request);
        } else {
            return $this->runCommand(
                $this->wrapCommand($command, $request, fn () => parent::runAction($command, $action, $request))
            );
        }
    }

    private function runCommand(SymfonyCommand $command): int
    {
        if (empty($this->symfonyApplication->running)) {
            $this->symfonyApplication->add($command);

            $this->symfonyApplication->running = true;

            return $this->symfonyApplication->run($this->input, $this->output);
        } else {
            $command->setApplication($this->symfonyApplication);

            return $command->run($this->input, $this->output);
        }
    }

    private function wrapCommand(Command $command, ConsoleRequestInterface $request, Closure $callback): SymfonyCommand
    {
        return new class ($command, $request, $callback) extends SymfonyCommand
        {
            private Command $command;
            private Closure $callback;

            public function __construct(Command $command, ConsoleRequestInterface $request, Closure $callback)
            {
                $this->command = $command;
                $this->callback = $callback;

                parent::__construct($request->getRoute());
            }

            protected function configure(): void
            {
                $definitions = $this->command->getDefinitions();
                if (isset($definitions[$this->command->actionId])) {
                    $this
                        ->setDefinition($definitions[$this->command->actionId]->getDefinition())
                        ->setDescription($definitions[$this->command->actionId]->getDescription())
                        ->setHelp($definitions[$this->command->actionId]->getHelp());
                }
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                $response = call_user_func($this->callback);
                if (!$response instanceof ConsoleResponseInterface) {
                    throw new LogicException(sprintf('Return value of %s::%s() must implement interface %s', get_class($this->command), $this->getName(), ConsoleResponseInterface::class));
                }
                return $response->getCode();
            }
        };
    }
}
