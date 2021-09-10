<?php

declare(strict_types=1);

namespace Ep\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
abstract class Bootstrap
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    abstract public function onScan(): void;

    abstract public function onStart(): void;
}
