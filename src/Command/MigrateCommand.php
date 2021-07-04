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

        $this->setDefinition('create', [
            new InputArgument('name', InputArgument::REQUIRED, 'Migration name'),
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'Save path'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name'),
        ])
            ->setDescription('Create an empty migration');

        $this->setDefinition('init', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'Save path'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name'),
            new InputOption('db', null, InputOption::VALUE_REQUIRED, 'Db name'),
            new InputOption('prefix', null, InputOption::VALUE_REQUIRED, 'Table prefix')
        ])
            ->setDescription('Initialize all tables');

        $this->setDefinition('list', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'Save path'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name'),
            new InputOption('db', null, InputOption::VALUE_REQUIRED, 'Db name'),
        ])
            ->setDescription('Print list of all migrations');

        $this->setDefinition('up', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'Save path'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name'),
            new InputOption('db', null, InputOption::VALUE_REQUIRED, 'Db name'),
            new InputOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migrations to apply'),
            new InputOption('all', null, InputOption::VALUE_NONE, 'Whether apply all migrations')
        ])
            ->setDescription('Execute all new migrations');

        $this->setDefinition('down', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'Save path'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'App name'),
            new InputOption('db', null, InputOption::VALUE_REQUIRED, 'Db name'),
            new InputOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migtions to downgrade'),
            new InputOption('all', null, InputOption::VALUE_NONE, 'Whether downgrade all migration history')
        ])
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
