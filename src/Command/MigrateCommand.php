<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\MigrateService;
use Ep\Console\Command;
use Symfony\Component\Console\Input\InputOption;

final class MigrateCommand extends Command
{
    private MigrateService $service;

    public function __construct(MigrateService $service)
    {
        $this->service = $service;

        $this->setDefinition('new', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations')
        ])
            ->setDescription('Create migration template');

        $this->setDefinition('ddl', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations'),
            new InputOption('prefix', null, InputOption::VALUE_REQUIRED, 'The table prefix')
        ])
            ->setDescription('Initialize DDL');

        $this->setDefinition('all', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations'),
        ])
            ->setDescription('Upgrades all migrations');

        $this->setDefinition('up', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations'),
            new InputOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migrations to apply')
        ])
            ->setDescription('Upgrades new migrations');

        $this->setDefinition('down', [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The save path of migrations'),
            new InputOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migtions to downgrade')
        ])
            ->setDescription('Downgrades old migrations');
    }

    /**
     * 创建一个迁移记录
     */
    public function newAction(): int
    {
        $this->service->new();

        return $this->success();
    }

    /**
     * 初始化所有表结构
     */
    public function ddlAction(): int
    {
        $this->service->ddl();

        return $this->success();
    }

    /**
     * 更新所有迁移
     */
    public function allAction(): int
    {
        $this->service->all();

        return $this->success();
    }

    /**
     * 执行所有还未同步的迁移
     */
    public function upAction(): int
    {
        $this->service->up();

        return $this->success();
    }

    /**
     * 回退已执行过的迁移
     */
    public function downAction(): int
    {
        $this->service->down();

        return $this->success();
    }
}
