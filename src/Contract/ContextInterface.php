<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep\Base\View;

/**
 * @property string $id
 */
interface ContextInterface
{
    public function getView(): View;
}
