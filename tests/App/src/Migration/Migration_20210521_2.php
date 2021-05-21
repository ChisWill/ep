<?php

declare(strict_types=1);

namespace Ep\Tests\App\Migration;

use Ep\Command\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class Migration_20210521_2 implements MigrateInterface
{
    public function up(MigrateBuilder $builder): void
    {
    }

    public function down(MigrateBuilder $builder): void
    {
    }
}
