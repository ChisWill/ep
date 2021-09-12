<?php

declare(strict_types=1);

namespace Ep\Annotation;

use Ep\Result\RouteResult;

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

    public static function handlers(): array
    {
        return [
            RouteResult::class
        ];
    }
}
