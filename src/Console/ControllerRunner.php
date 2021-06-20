<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\ControllerRunner as BaseControllerRunner;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
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
    private ConsoleResponseInterface $response;

    public function __construct(
        ContainerInterface $container,
        SymfonyApplication $symfonyApplication,
        ConsoleResponseInterface $response
    ) {
        parent::__construct($container);

        $this->symfonyApplication = $symfonyApplication;
        $this->response = $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function createController(string $class, string $actionId, $request): ControllerInterface
    {
        $command = parent::createController($class, $actionId, $request);

        $this->symfonyApplication->add($this->wrapCommand($command, $this->createAction($actionId), $request));

        return $command;
    }

    /**
     * {@inheritDoc}
     */
    protected function runAction(ControllerInterface $command, string $action, $request): ConsoleResponseInterface
    {
        return $this->response->withCode(
            $this->symfonyApplication->run($request->getInput(), $this->response->getOutput())
        );
    }

    private function wrapCommand(Command $command, string $action, ConsoleRequestInterface $request): SymfonyCommand
    {
        return new class ($command, $request, fn () => parent::runAction($command, $action, $request)) extends SymfonyCommand
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
                /** @var CommandDefinition[] $definitions */
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
                return call_user_func($this->callback)->getCode();
            }
        };
    }
}
