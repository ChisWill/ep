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
     * @param ReflectionProperty $reflector
     */
    public function process(object $instance, Reflector $reflector, array $arguments = []): void
    {
        $className = $reflector->getType()->getName();

        $target = $this->getTargetInstanceFromArguments($arguments, $className) ?? Ep::getDi()->get($className);

        if ($this->properties) {
            $target = clone $target;
            foreach ($this->properties as $name => $value) {
                $target->$name = $value;
            }
        }

        $reflector->setAccessible(true);
        $reflector->setValue($instance, $target);
    }

    private function getTargetInstanceFromArguments(array $arguments, string $className): ?object
    {
        foreach ($arguments as $value) {
            if (is_object($value) && is_subclass_of($value, $className, false)) {
                return $value;
            }
        }
        return null;
    }
}
