<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Validator\Rule\{
    Number,
    Required,
    HasLength,
};

/**
 * @property int $id
 * @property int $school_id
 * @property string $name
 */
class Classes extends ActiveRecord
{
    public const PK = 'id';

    public function tableName(): string
    {
        return '{{%class}}';
    }

    final protected function rules(): array
    {
        return $this->userRules() + [
            'school_id' => [
                (Number::rule())->integer(),
                (Required::rule()),
            ],
            'name' => [
                (HasLength::rule())->max(50),
                (Required::rule()),
            ],
        ];
    }

    protected function userRules(): array
    {
        return [];
    }

    public function getSchool(): ActiveQuery
    {
        return $this->hasOne(School::class, ['id' => 'school_id']);
    }
}
