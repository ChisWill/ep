<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use Ep\Db\ActiveRecord;
<?= $rules[0] ?>

/**
<?php foreach ($columns as $field => $column): ?>
<?= ' * @property ' . $typecast($column->getPhpType()) . ' $' . $field . ($column->getComment() ? ' ' . $column->getComment() : '') . "\n" ?>
<?php endforeach ?> */
class <?= $className ?> extends ActiveRecord
{
    public const PK = <?= $primaryKey ?>;

    public function tableName(): string
    {
        return '{{%<?= $tableName ?>}}';
    }

    public function rules(): array
    {
<?php if ($rules[1]): ?>
        return [
<?php foreach ($rules[1] as $field => $items): ?>
            '<?= $field ?>' => [
    <?php foreach ($items as $rule): ?>
            <?= $rule ?>,
    <?php endforeach ?>
        ],
<?php endforeach ?>
        ];
<?php else: ?>
        return [];
<?php endif ?>
    }
}
