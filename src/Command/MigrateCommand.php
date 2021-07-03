<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\MigrateService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Symfony\Component\Console\Input\InputOption;

final class MigrateCommand extends Command
{
    private MigrateService $service;

    public function __construct(MigrateService $service)
    {
        $this->service = $service;

        $this->setDefinition('new', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'The app name'),
        ])
            ->setDescription('Create migration template');

        $this->setDefinition('ddl', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'The app name'),
            new InputOption('db', null, InputOption::VALUE_REQUIRED, 'The db name'),
            new InputOption('prefix', null, InputOption::VALUE_REQUIRED, 'The table prefix')
        ])
            ->setDescription('Initialize DDL');

        $this->setDefinition('up', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'The app name'),
            new InputOption('db', null, InputOption::VALUE_REQUIRED, 'The db name'),
            new InputOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migrations to apply'),
            new InputOption('all', null, InputOption::VALUE_NONE, 'Whether apply all migrations')
        ])
            ->setDescription('Upgrades new migrations');

        $this->setDefinition('down', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations'),
            new InputOption('app', 'a', InputOption::VALUE_REQUIRED, 'The app name'),
            new InputOption('db', null, InputOption::VALUE_REQUIRED, 'The db name'),
            new InputOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migtions to downgrade'),
            new InputOption('all', null, InputOption::VALUE_NONE, 'Whether downgrade all migration history')
        ])
            ->setDescription('Downgrades old migrations');
    }

    public function newAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->init($request->getOptions());

        $this->service->new();

        return $this->success();
    }

    public function ddlAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        $this->service->init($request->getOptions());

        $this->service->ddl();

        return $this->success();
    }

    public function upAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        if ($request->getOption('all') && !$this->confirm('Are you sure apply all migrations?')) {
            return $this->success('Skipped.');
        }

        $this->service->init($request->getOptions());

        $this->service->up();

        return $this->success();
    }

    public function downAction(ConsoleRequestInterface $request): ConsoleResponseInterface
    {
        if ($request->getOption('all') && !$this->confirm('Are you sure downgrade all migrations?')) {
            return $this->success('Skipped.');
        }

        $this->service->init($request->getOptions());

        $this->service->down();

        return $this->success();
    }
}
