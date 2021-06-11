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
     * @param ReflectionProperty $reflector
     */
    public function process(object $instance, Reflector $reflector, array $arguments = [])
    {
        $reflector->setAccessible(true);

        $objects = [];
        foreach ($arguments as $item) {
            if (is_object($item)) {
                $objects[get_class($item)] = $item;
            }
        }

        $class = $reflector->getType()->getName();

        $reflector->setValue($instance, $objects[$class] ??  Ep::getDi()->get($class));
    }
}
