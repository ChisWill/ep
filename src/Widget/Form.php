<?php

declare(strict_types=1);

namespace Ep\Widget;

use Yiisoft\Arrays\ArrayAccessTrait;
use Yiisoft\Validator\DataSetInterface;
use ArrayAccess;

abstract class Form implements ArrayAccess, DataSetInterface
{
    use ArrayAccessTrait;
    use FormTrait;

    private array $data = [];

    /**
     * @return mixed
     */
    public function __get(string $name)
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
