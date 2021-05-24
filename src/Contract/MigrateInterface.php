<?php

declare(strict_types=1);

namespace Ep\Contract;

use Ep\Command\Helper\MigrateBuilder;

interface MigrateInterface
{
    public function up(MigrateBuilder $builder): void;

    public function down(MigrateBuilder $builder): void;
}
