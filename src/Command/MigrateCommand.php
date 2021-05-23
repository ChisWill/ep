<?php

declare(strict_types=1);

namespace Ep\Command;

use Ep\Console\Command;
use Ep\Contract\ConsoleRequestInterface;
use Throwable;

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

    /**
     * 创建一个迁移记录
     */
    public function newAction(ConsoleRequestInterface $request): string
    {
        try {
            $this->service->init($request->getParams());

            return $this->service->new();
        } catch (Throwable $t) {
            return $t->getMessage();
        }
    }

    /**
     * 初始化所有表结构
     */
    public function ddlAction(ConsoleRequestInterface $request): string
    {
        try {
            $this->service->initDDL($request->getParams());

            return $this->service->ddl();
        } catch (Throwable $t) {
            return $t->getMessage();
        }
    }

    /**
     * 更新所有迁移
     */
    public function allAction(ConsoleRequestInterface $request): string
    {
        try {
            $this->service->init($request->getParams());

            return $this->service->all();
        } catch (Throwable $t) {
            return $t->getMessage();
        }
    }

    /**
     * 执行所有还未同步的迁移
     */
    public function upAction(ConsoleRequestInterface $request): string
    {
        try {
            $this->service->init($request->getParams());

            return $this->service->up();
        } catch (Throwable $t) {
            return $t->getMessage();
        }
    }

    /**
     * 回退已执行过的迁移
     */
    public function downAction(ConsoleRequestInterface $request): string
    {
        try {
            $this->service->init($request->getParams());

            return $this->service->down();
        } catch (Throwable $t) {
            return $t->getMessage();
        }
    }
}
