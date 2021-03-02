<?php

declare(strict_types=1);

namespace Ep\Tests\App\Model;

use Ep\Db\ActiveRecord;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\InRange;
use Yiisoft\Validator\Rule\MatchRegularExpression;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\ValidationContext;

/**
 * @property int $id
 * @property string $username
 * @property int $age
 * @property string $birthday
 */
class User extends ActiveRecord
{
    public function tableName(): string
    {
        return 'user';
    }

    public function rules(string $scene = null): array
    {
        return [
            'age' => [
                (new Number)->integer()->max(99)->tooBigMessage('最多99岁')->skipOnEmpty(true),
                (new InRange([10, 20, 30]))->skipOnEmpty(true)
            ],
            'username' => [(new HasLength)->max(8)->min(2)->tooLongMessage('用户名最多8个字')->tooShortMessage('最少2个字')],
            'password' => [(new HasLength)->max(6)->tooLongMessage('最多6个'), (new MatchRegularExpression('/^[a-z\d]{4,6}$/i'))->message('4-8个字符')],
            'birthday' => [$this->rule([self::class, 'checkDate'])]
        ];
    }

    public static function checkDate($value, Result $result, ?ValidationContext $context = null): void
    {
        if (strtotime($value) === false) {
            $result->addError('生日时间格式不正确');
        }
    }
}
