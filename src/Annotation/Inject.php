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

        $className = $reflector->getType()->getName();

        foreach ($arguments as $argv) {
            if (is_object($argv) && is_subclass_of($argv, $className, false)) {
                $target = $argv;
                break;
            }
        }
        $target ??= Ep::getDi()->get($className);

        if ($this->properties) {
            $target = clone $target;
            foreach ($this->properties as $name => $value) {
                $target->$name = $value;
            }
        }

        $reflector->setValue($instance, $target);
    }
}
