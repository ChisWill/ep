<?php

declare(strict_types=1);

namespace Ep\Tests\App\Migration;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class Migration_20210523_2 implements MigrateInterface
{
    public function up(MigrateBuilder $builder): void
    {
        $builder->createTable('story', [
            'id' => $builder->primaryKey(),
            'title' => $builder->string(50)->notNull(),
            'desc' => $builder->string(100)->defaultValue(''),
            'content' => $builder->text()
        ]);
    }

    public function down(MigrateBuilder $builder): void
    {
        $builder->dropTable('story');
    }
}
