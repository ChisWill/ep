<?php

declare(strict_types=1);

namespace Ep\Widget;

use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Url;

abstract class Form implements DataSetInterface
{
    use FormTrait;

    protected Required $required;
    protected HasLength $hasLength;
    protected Number $number;
    protected Url $url;
    protected Email $email;

    public function __construct(
        Required $required,
        HasLength $hasLength,
        Number $number,
        Url $url,
        Email $email
    ) {
        $this->required = $required;
        $this->hasLength = $hasLength;
        $this->number = $number;
        $this->url = $url;
        $this->email = $email;
    }

    private array $data = [];

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function load(array $data, string $scope = null): bool
    {
        if ($data) {
            if ($scope !== null) {
                $this->data = $data[$scope] ?? [];
            } else {
                $this->data = $data;
            }
            return true;
        } else {
            return false;
        }
    }

    public function getAttributes(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAttribute(string $attribute): bool
    {
        return isset($this->data[$attribute]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeValue(string $attribute)
    {
        return $this->data[$attribute];
    }
}
