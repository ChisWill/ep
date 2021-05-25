<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class <?= $className ?> implements MigrateInterface
{
    public function up(MigrateBuilder $builder): void
    {
        $builder->execute(<<<'DDL'
<?= $ddl ?>
DDL);
    }

    public function down(MigrateBuilder $builder): void
    {
    }
}
