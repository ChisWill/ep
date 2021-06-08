<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class <?= $className ?> implements MigrateInterface
{
    public function up(MigrateBuilder $builder): void
    {
<?php if ($upSql): ?>
        $builder->execute(<<<'DDL'
<?= $upSql ?>
DDL);
<?php endif ?>
    }

    public function down(MigrateBuilder $builder): void
    {
<?= $downSql ?>
    }
}
