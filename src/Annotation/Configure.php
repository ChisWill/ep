<?php

declare(strict_types=1);

namespace Ep\Annotation;

abstract class Configure
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $this->normalize($values);
    }

    public function getValues(): array
    {
        return $this->values;
    }

    protected function normalize(array $values): array
    {
        return $values;
    }

    abstract public static function handler(): string;
}
