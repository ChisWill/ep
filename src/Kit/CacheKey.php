<?php

declare(strict_types=1);

namespace Ep\Kit;

final class CacheKey
{
    public function classAnnotation(string $class): string
    {
        return 'Ep-Class-Annotation-' . rawurlencode($class);
    }
}
