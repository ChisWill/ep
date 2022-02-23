<?php

declare(strict_types=1);

namespace Ep\Widget;

use Yiisoft\Validator\Error;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Validator;

trait FormTrait
{
    private Result $result;

    public function validate(): bool
    {
        $this->result = (new Validator())->validate($this, $this->rules());

        return $this->result->isValid();
    }

    public function getErrors(): array
    {
        return $this->result->getErrorMessages();
    }

    abstract protected function rules(): array;
}
