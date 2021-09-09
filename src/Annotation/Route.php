<?php

declare(strict_types=1);

namespace Ep\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Route
{
    public function __construct(array $values)
    {
        // todo
        // tt($values);
    }
}
