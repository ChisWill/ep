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

final class ListCommand extends Command
{
    private SymfonyApplication $symfonyApplication;
    private SymfonyCommand $listCommand;
    private HelpService $service;

    public function __construct(SymfonyApplication $symfonyApplication, HelpService $service)
    {
        $this->symfonyApplication = $symfonyApplication;
        $this->listCommand = $symfonyApplication->find('list');
        $this->service = $service;

        $this
            ->setDefinition('index', [
                new InputArgument('namespace', InputArgument::OPTIONAL, 'The namespace name'),
                new InputOption('raw', null, InputOption::VALUE_NONE, 'To output raw command list'),
                new InputOption('format', null, InputOption::VALUE_REQUIRED, 'The output format (txt, xml, json, or md)', 'txt'),
                new InputOption('short', null, InputOption::VALUE_NONE, 'To skip describing commands\' arguments'),
            ])
            ->setDescription('List commands')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command lists all commands:

    <info>%command.full_name%</info>

You can also display the commands for a specific namespace:

    <info>%command.full_name% test</info>

You can also output the information in other formats by using the <comment>--format</comment> option:

    <info>%command.full_name% --format=xml</info>

It's also possible to get raw list of commands (useful for embedding command runner):

    <info>%command.full_name% --raw</info>
EOF);
    }

    public function indexAction(ConsoleRequestInterface $request, OutputInterface $output): ConsoleResponseInterface
    {
        $this->service->init($request->getOptions());

        $commands = $this->service->getAllCommands();
        array_walk($commands, [$this->symfonyApplication, 'add']);

        $arguments = [
            'namespace' => $request->getArgument('namespace'),
            '--raw' => $request->getOption('raw'),
            '--format' => $request->getOption('format'),
            '--short' => $request->getOption('short')
        ];
        return $this->getService()->status(
            $this->listCommand->run(new ArrayInput($arguments), $output)
        );
    }
}
