<?php

declare(strict_types=1);

namespace Ep\Tests\App\Migration;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class Migration_20210523_1 implements MigrateInterface
{
    public function up(MigrateBuilder $builder): void
    {
        $builder->createTable('student', [
            'id' => $builder->primaryKey(),
            'class_id' => $builder->integer()->notNull(),
            'name' => $builder->string(50)->notNull(),
            'age' => $builder->smallInteger()->defaultValue(0),
            'birthday' => $builder->dateTime(),
            'sex' => $builder->tinyInteger()->defaultValue(0)
        ]);

        $builder->createIndex('student_name', 'student', ['class_id', 'name']);

        $builder->createTable('class', [
            'id' => $builder->primaryKey(),
            'name' => $builder->string(50)->notNull()
        ]);
        $builder->createIndex('class_name', 'class', 'name');

        $builder->batchInsert('class', ['name'], [
            ['I-7'],
            ['II-1'],
            ['II-2'],
        ]);
        $classes = $builder->find()->from('class')->where(['name' => ['I-7', 'II-1', 'II-2']])->map('name', 'id');

        $builder->batchInsert('student', ['class_id', 'name', 'age', 'birthday', 'sex'], [
            [$classes['I-7'], 'Rean Schwarzer', '17', '2003-1-1', 1],
            [$classes['I-7'], 'Alisa Reinford', '16', '2004-2-2', 2],
            [$classes['I-7'], 'Fei Claussell', '15', '2005-3-3', 2],
            [$classes['II-1'], 'Crow Armbrust', '19', '2001-4-4', 1],
            [$classes['II-2'], 'Angelica Rogner', '20', '2000-5-5', 2],
        ]);
    }

    public function down(MigrateBuilder $builder): void
    {
        $builder->dropTable('student');
        $builder->dropTable('class');
    }
}
