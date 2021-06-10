<?php

declare(strict_types=1);

namespace Ep\Tests\App\Migration;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class DDL implements MigrateInterface
{
    public function up(MigrateBuilder $builder): void
    {
        $builder->execute(<<<'DDL'
CREATE TABLE "school" (
"id"  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
"name"  TEXT NOT NULL
);
DDL);
    }

    public function down(MigrateBuilder $builder): void
    {
        $builder->dropTable('school');
    }
}
