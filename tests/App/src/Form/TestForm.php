<?php

declare(strict_types=1);

namespace Ep\Tests\App\Form;

use Ep\Widget\Form;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;

/**
 * @property string $username
 * @property string $password
 * @property int $age
 * @property int $age
 */
class TestForm extends Form
{
    protected function rules(): array
    {
        return [
            'username' => [new Required()],
            'password' => [(new HasLength())->min(3)],
            'age' => [(new Number())->integer()->skipOnEmpty(true)],
            'title' => [(new HasLength())->max(5)->min(1)]
        ];
    }
}
