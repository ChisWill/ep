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
    public const PK = ['id', 'gid'];

    public function tableName(): string
    {
        return '{{%user_parent}}';
    }

    final protected function rules(): array
    {
        return $this->userRules() + [
            'username' => [
                (HasLength::rule())->max(50)->skipOnEmpty(true),
            ],
            'age' => [
                (Number::rule())->integer()->skipOnEmpty(true),
            ],
        ];
    }

    protected function userRules(): array
    {
        return [];
    }
}
