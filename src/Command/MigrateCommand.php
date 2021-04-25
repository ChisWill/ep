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
     * 初始化所有表结构
     */
    public function initAction(ConsoleRequestInterface $request): string
    {
        $db = $request->getParams()['db'] ?? '';

        try {
            return $this->service->init($db);
        } catch (Throwable $t) {
            return $t->getMessage();
        }
    }

    /**
     * 创建一个迁移记录
     */
    public function createAction(): string
    {
        return 'create';
    }

    /**
     * 执行所有还未同步的迁移
     */
    public function updateAction(): string
    {
        return 'update';
    }
}
