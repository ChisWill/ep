<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\ControllerRunner as BaseControllerRunner;
use Ep\Contract\ControllerInterface;

final class ControllerRunner extends BaseControllerRunner
{
    /**
     * {@inheritDoc}
     */
    protected function runAction(ControllerInterface $controller, string $action, $request)
    {
        return parent::runAction($controller, $action, $request);
    }

    // private function rr(Command $command, string $action, ConsoleRequest $request): int
    // {
    //     $this->symfonyApplication->setName('Ep');
    //     $this->symfonyApplication->setVersion(Ep::VERSION);

    //     $symfonyCommand = $this->wrapSymfonyCommand($command, $action);
    //     // $this->symfonyApplication->add($symfonyCommand);

    //     return $this->symfonyApplication->run();
    // }

    // private function wrapSymfonyCommand(Command $command, string $action): SymfonyCommand
    // {
    //     return new class ($command, $action) extends SymfonyCommand
    //     {
    //         private Command $command;
    //         private string $action;

    //         public function __construct(Command $command, string $action)
    //         {
    //             $this->command = $command;
    //             $this->action = $action;

    //             parent::__construct('help/tes');
    //         }

    //         protected function execute(InputInterface $input, OutputInterface $output): int
    //         {
    //             $message = call_user_func([$this->command, $this->action]);
    //             $output->writeln($message);
    //             return 0;
    //         }
    //     };
    // }
}
