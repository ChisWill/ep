<?php

declare(strict_types=1);

namespace Ep\Kit;

final class CacheKey
{
    public static function getAnnotationKey(string $class): string
    {
        return 'Ep-Annotation-' . rawurlencode($class);
    }
}
