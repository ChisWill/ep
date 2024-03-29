<?php

declare(strict_types=1);

namespace Ep\Annotation;

use Ep\Base\Route as BaseRoute;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Route extends Configure
{
    protected function normalize(array $values): array
    {
        $value = $values['value'] ?? '';
        if (is_array($value)) {
            return [
                'value' => array_shift($value),
                'method' => array_shift($value) ?? null
            ];
        } else {
            $method = $values['method'] ?? null;
            return compact('value', 'method');
        }
    }

    public static function handler(): string
    {
        return BaseRoute::class;
    }
}
