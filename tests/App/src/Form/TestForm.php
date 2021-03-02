<?php

declare(strict_types=1);

namespace Ep\Tests\App\Form;

use Ep\Widget\Form;

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
            'username' => [$this->required],
            'password' => [$this->hasLength->min(3)],
            'age' => [$this->number->integer()->skipOnEmpty(true)],
            'title' => [$this->hasLength->max(5)->min(1)]
        ];
    }
}
