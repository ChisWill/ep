<?php

declare(strict_types=1);

namespace Ep\Standard;

interface ContextInterface
{
    /**
     * 获取上下文代号，默认获取短名称
     */
    public function getId(bool $short = true): string;
}
