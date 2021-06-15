<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\Validator\Rule\{
    HasLength,
    Number,
};

/**
 * @property int $id
 * @property int $gid
 * @property string $username
 * @property int $age
 */
class UserParent extends ActiveRecord
{
    public function tableName(): string
    {
        return '{{%user_parent}}';
    }

    public function rules(): array
    {
        return [
            'username' => [
                (HasLength::rule())->max(255)->skipOnEmpty(true),
            ],
            'age' => [
                (Number::rule())->integer()->skipOnEmpty(true),
            ],
        ];
    }
}
