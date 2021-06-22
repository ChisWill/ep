<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\HelpService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class HelpCommand extends Command
{
    private SymfonyApplication $symfonyApplication;
    private SymfonyCommand $helpCommand;
    private HelpService $service;

    public function __construct(SymfonyApplication $symfonyApplication, HelpService $service)
    {
        $this->symfonyApplication = $symfonyApplication;
        $this->helpCommand = $symfonyApplication->find('help');
        $this->service = $service;

        $this->setDefinition('index', [
            new InputArgument('name', InputArgument::REQUIRED, 'The command name'),
            new InputOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (txt, xml, json, or md)', 'txt'),
            new InputOption('raw', null, InputOption::VALUE_NONE, 'To output raw command help'),
        ])
            ->setDescription('Display help for a command')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command displays help for a given command:

    <info>%command.full_name% list</info>

You can also output the help in other formats by using the <comment>--format</comment> option:

    <info>%command.full_name% --format=xml list</info>

To display the list of available commands, please use the <info>list</info> command.
EOF);
    }

    public function indexAction(ConsoleRequestInterface $request, OutputInterface $output): ConsoleResponseInterface
    {
        $this->service->init($request->getOptions());

        $commands = $this->service->getAllCommands();
        array_walk($commands, [$this->symfonyApplication, 'add']);

        $arguments = [
            'command_name' => $request->getArgument('name'),
            '--format' => $request->getOption('format'),
            '--raw' => $request->getOption('raw')
        ];
        return $this->getService()->status(
            $this->helpCommand->run(new ArrayInput($arguments), $output)
        );
    }
}
