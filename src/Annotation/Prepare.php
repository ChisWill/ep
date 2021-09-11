<?php

declare(strict_types=1);

namespace Ep\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
abstract class Prepare
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    abstract public function prepare(): void;
}
