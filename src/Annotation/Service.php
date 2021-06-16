<?php

declare(strict_types=1);

namespace Ep\Annotation;

use Ep;
use Ep\Contract\AnnotationInterface;
use ReflectionProperty;
use Reflector;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Service implements AnnotationInterface
{
    /**
     * @param  ReflectionProperty $reflector
     */
    public function process(object $instance, Reflector $reflector, array $arguments = []): void
    {
        $reflector->setAccessible(true);

        $class = $reflector->getType()->getName();

        foreach ($arguments as $item) {
            if (is_object($item) && is_subclass_of($item, $class, false)) {
                $value = $item;
                break;
            }
        }

        $reflector->setValue($instance, $value ?? Ep::getDi()->get($class));
    }
}
