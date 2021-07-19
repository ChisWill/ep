<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Validator\Rule\Required;

/**
 * @property int $id
 * @property string $name
 */
class School extends ActiveRecord
{
    public const PK = 'id';

    public function tableName(): string
    {
        return '{{%school}}';
    }

    final protected function rules(): array
    {
        return $this->userRules() + [
            'name' => [
                (Required::rule()),
            ],
        ];
    }

    protected function userRules(): array
    {
        return [];
    }

    public function getClasses(): ActiveQuery
    {
        return $this->hasMany(Classes::class, ['school_id' => 'id']);
    }
}
