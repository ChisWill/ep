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
        foreach ((new Validator)->validate($this, $this->rules()) as $attribute => $result) {
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

    protected function rule(callable $callback): Rule
    {
        return new class ($callback) extends Rule
        {
            /**
             * @param callback $callback
             */
            private $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            protected function validateValue($value, ?ValidationContext $context = null): Result
            {
                $result = new Result;
                call_user_func($this->callback, $value, $result, $context);
                return $result;
            }
        };
    }

    /**
     * @return Rule[][] $rules
     */
    abstract protected function rules(): array;
}
