<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class <?= $className ?> implements MigrateInterface
{
    public static function getName(): string
    {
        return '<?= $name ?>';
    }

    public function up(MigrateBuilder $builder): void
    {
<?php if ($upSql): ?>
        $builder->execute(<<<'DDL'
<?= $upSql ?>
DDL);
<?php endif ?>

<?php if ($insertData): ?>
<?php foreach ($insertData as $tableName => $value): ?>
        $builder->batchInsert('<?= $tableName ?>', ['<?= implode("', '", $value['columns']) ?>'], [
<?php foreach ($value['rows'] as $row): ?>
            ['<?= implode("', '", array_map(static fn ($v): string => addcslashes($v, "'"), array_values($row))) ?>'],
<?php endforeach ?>
        ]);
<?php endforeach ?>
<?php endif ?>
    }

    public function down(MigrateBuilder $builder): void
    {
<?= $downSql ?>
    }
}
