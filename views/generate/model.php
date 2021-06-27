<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

<?= $use ?>

/**
<?= $property ?>
 */
class <?= $className ?> extends ActiveRecord
{
    public const PK = <?= $primaryKey ?>;

    public function tableName(): string
    {
        return '{{%<?= $tableName ?>}}';
    }

    final protected function rules(): array
    {
        <?= $rules ?>
    }

    protected function userRules(): array
    {
        return [];
    }
}
