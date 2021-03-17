<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\FilterTrait;
use Ep\Contract\ModuleInterface;

abstract class Module implements ModuleInterface
{
    use FilterTrait;
}
