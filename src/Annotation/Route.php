<?php

declare(strict_types=1);

namespace Ep\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Route extends Bootstrap
{
    public function __construct(array $values)
    {
        // todo
        // tt($values);
    }

    public function onScan(): void
    {
    }

    public function onStart(): void
    {
    }
}
