<?php

declare(strict_types=1);

namespace Ep\Widget;

use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\Validator;

trait FormTrait
{
    private array $_errors = [];

    public function validate(): bool
    {
        $this->_errors = [];
        foreach ((new Validator())->validate($this, $this->rules()) as $attribute => $result) {
            if (!$result->isValid()) {
                $this->_errors[$attribute] = $result->getErrors();
            }
        }
        return empty($this->_errors);
    }

    public function getErrors(): array
    {
        return $this->_errors;
    }

    /**
     * @return Rule[][] $rules
     */
    abstract protected function rules(): array;
}
