<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\MigrateService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

final class MigrateCommand extends Command
{
    private MigrateService $service;

    public function __construct(MigrateService $service)
    {
        $this->service = $service;

        $this
            ->createDefinition('create')
            ->addArgument('name', InputArgument::REQUIRED, 'Migration name')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name')
            ->setDescription('Create an empty migration');

        $this
            ->createDefinition('init')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'Table prefix')
            ->addOption('data', null, InputOption::VALUE_NONE, 'Whether initialize table data')
            ->setDescription('Initialize all tables');

        $this
            ->createDefinition('list')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->setDescription('Print list of all migrations');

        $this
            ->createDefinition('up')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->addOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migrations to apply')
            ->setDescription('Execute all new migrations');

        $this
            ->createDefinition('down')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Save path')
            ->addOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name')
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'Db name')
            ->addOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migtions to downgrade')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Whether downgrade all migration history')
            ->setDescription('Rollback last migration');
    }

    public function createAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->initialize($request->getOptions());

        $this->service->create($request->getArgument('name'));

        return $this->success();
    }

    public function initAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->initialize($request->getOptions());

        $this->service->init();

        return $this->success();
    }

    public function listAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->initialize($request->getOptions());

        $this->service->list();

        return $this->success();
    }

    public function upAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->initialize($request->getOptions());

        $this->service->up();

        return $this->success();
    }

    public function downAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->initialize($request->getOptions());

        $this->service->down();

        return $this->success();
    }
}
