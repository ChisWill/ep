<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\{
    Callback,
    Number,
    Required,
    HasLength,
    InRange,
    MatchRegularExpression,
};
use Yiisoft\Validator\ValidationContext;

/**
 * @property int $id
 * @property int $class_id
 * @property string $name
 * @property string $password
 * @property int $age
 * @property string $birthday
 * @property int $sex
 * @property string $desc
 */
class Student extends ActiveRecord
{
    public const PK = 'id';

    public function tableName(): string
    {
        return '{{%student}}';
    }

    final protected function rules(): array
    {
        return $this->userRules() + [
            'class_id' => [
                Required::rule(),
                Number::rule()->integer(),
            ],
            'name' => [
                Required::rule(),
                HasLength::rule()->max(50),
            ],
            'password' => [
                Required::rule(),
                HasLength::rule()->max(100),
            ],
            'age' => [
                Number::rule()->integer()->skipOnEmpty(true),
            ],
            'sex' => [
                Number::rule()->integer()->skipOnEmpty(true),
            ],
        ];
    }

    protected function userRules(): array
    {
        $ageRange = array_keys(array_fill(18, 30, 1));
        return [
            'age' => [
                (Number::rule())->integer()->max(99)->tooBigMessage('最多99岁')->skipOnEmpty(true),
                (InRange::rule($ageRange))->skipOnEmpty(true)->message(sprintf('Range is: %s', implode(', ', $ageRange)))
            ],
            'name' => [(HasLength::rule())->max(8)->min(2)->tooLongMessage('用户名最多8个字')->tooShortMessage('最少2个字')],
            'password' => [(HasLength::rule())->max(6)->tooLongMessage('最多6个'), (MatchRegularExpression::rule('/^[a-z\d]{4,6}$/i'))->message('4-8个字符')],
            'birthday' => [Callback::rule([self::class, 'checkDate'])]
        ];
    }

    public static function checkDate($value, ?ValidationContext $context = null): Result
    {
        $result = new Result();
        if (strtotime($value) === false) {
            $result->addError('生日时间格式不正确');
        }
        return $result;
    }

    public function getClass(): ActiveQuery
    {
        return $this->hasOne(Classes::class, ['id' => 'class_id']);
    }
}
