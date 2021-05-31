<?php

declare(strict_types=1);

namespace Ep\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

final class TestCommand extends Command
{
    // protected static $defaultName = 'test';

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('a1', InputArgument::OPTIONAL, 'The command name'),
            new InputOption('abc', ['a', 'b', 'c'], InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'lala'),
            // new InputArgument('a2', InputArgument::OPTIONAL, 'The command name 2'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $params = $input->getArgument('a1');
        $options = $input->getOptions();

        $output->writeln('<fg=red;options=bold>OK</>');

        return 0;
    }
}
