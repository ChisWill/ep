<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep\Base\View;

/**
 * @property string $id 上下文代号
 */
interface ContextInterface
{
    public function getView(): View;

    public function getViewPath(): string;
}
