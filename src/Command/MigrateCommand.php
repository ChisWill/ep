<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Command\Service\MigrateService;
use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * 数据库迁移
 */
final class MigrateCommand extends Command
{
    private MigrateService $service;

    public function __construct(MigrateService $service)
    {
        $this->service = $service;
    }

    public function definition(): array
    {
        return [
            new InputOption('path', null, InputOption::VALUE_REQUIRED, 'The path of migration'),
            new InputOption('step', null, InputOption::VALUE_REQUIRED, 'The number of migtions to apply'),
        ];
    }

    /**
     * 创建一个迁移记录
     */
    public function newAction(ConsoleRequestInterface $request): int
    {
        $this->service->init($request->getOptions());

        $this->service->new();

        return $this->success();
    }

    public function ddlDefinition(): array
    {
        return [
            new InputOption('prefix', null, InputOption::VALUE_REQUIRED, 'The table prefix'),
        ];
    }

    /**
     * 初始化所有表结构
     */
    public function ddlAction(ConsoleRequestInterface $request): int
    {
        $this->service->initDDL($request->getOptions());

        $this->service->ddl();

        return $this->success();
    }

    /**
     * 更新所有迁移
     */
    public function allAction(ConsoleRequestInterface $request): int
    {
        $this->service->init($request->getOptions());

        $this->service->all();

        return $this->success();
    }

    /**
     * 执行所有还未同步的迁移
     */
    public function upAction(ConsoleRequestInterface $request): int
    {
        $this->service->init($request->getOptions());

        $this->service->up();

        return $this->success();
    }

    /**
     * 回退已执行过的迁移
     */
    public function downAction(ConsoleRequestInterface $request): int
    {
        $this->service->initDown($request->getOptions());

        $this->service->down();

        return $this->success();
    }
}
