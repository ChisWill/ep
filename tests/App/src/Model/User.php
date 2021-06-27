<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\{
    Number,
    HasLength,
    Email,
};
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\InRange;
use Yiisoft\Validator\Rule\MatchRegularExpression;

/**
 * @property string $id
 * @property int $pid
 * @property string $name 名字
 * @property string $username
 * @property string $password
 * @property int $sex 性别
 * @property string $birthday
 * @property string $age 年龄
 * @property string $email 邮箱
 */
class User extends ActiveRecord
{
    public const PK = 'id';

    public function tableName(): string
    {
        return 'user';
    }

    final protected function rules(): array
    {
        return $this->userRules() + [
            'pid' => [
                (Number::rule())->integer()->skipOnEmpty(true),
            ],
            'name' => [
                (HasLength::rule())->max(255)->skipOnEmpty(true),
            ],
            'username' => [
                (HasLength::rule())->max(220)->skipOnEmpty(true),
            ],
            'password' => [
                (HasLength::rule())->max(200)->skipOnEmpty(true),
            ],
            'sex' => [
                (Number::rule())->integer()->skipOnEmpty(true),
            ],
            'age' => [
                (Number::rule())->skipOnEmpty(true),
            ],
            'email' => [
                (HasLength::rule())->max(255)->skipOnEmpty(true),
                (Email::rule())->skipOnEmpty(true),
            ],
        ];
    }

    protected function userRules(): array
    {
        return [
            'age' => [
                (Number::rule())->integer()->max(99)->tooBigMessage('最多99岁')->skipOnEmpty(true),
                (InRange::rule([10, 20, 30]))->skipOnEmpty(true)
            ],
            'username' => [(HasLength::rule())->max(8)->min(2)->tooLongMessage('用户名最多8个字')->tooShortMessage('最少2个字')],
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

    public function getParent()
    {
        return $this->hasOne(UserParent::class, ['id' => 'pid']);
    }
}
