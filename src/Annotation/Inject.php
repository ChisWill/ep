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
final class Inject implements AnnotationInterface
{
    private array $properties = [];

    public function __construct(array $values)
    {
        $this->properties = $values;
    }

    /**
     * @param  ReflectionProperty $reflector
     */
    public function process(object $instance, Reflector $reflector, array $arguments = []): void
    {
        $reflector->setAccessible(true);

        $class = $reflector->getType()->getName();

        foreach ($arguments as $item) {
            if (is_object($item) && is_subclass_of($item, $class, false)) {
                $target = $item;
                break;
            }
        }
        $target ??= Ep::getDi()->get($class);

        if ($this->properties) {
            $target = clone $target;
            foreach ($this->properties as $name => $value) {
                $target->$name = $value;
            }
        }

        $reflector->setValue($instance, $target);
    }
}
