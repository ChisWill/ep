<?php

declare(strict_types=1);

namespace Ep\Tests\App\Migration;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class Initialization implements MigrateInterface
{
    public static function getName(): string
    {
        return 'Initialization';
    }

    public function up(MigrateBuilder $builder): void
    {
        $builder->execute(<<<'DDL'
CREATE TABLE "school" (
	 "id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	 "name" TEXT NOT NULL
);
DDL);

        $builder->batchInsert('school', ['id', 'name'], [
            ['1', '托尔兹士官学院'],
            ['2', '警察学院'],
        ]);
    }

    public function down(MigrateBuilder $builder): void
    {
        $builder->dropTable('school');
    }
}
