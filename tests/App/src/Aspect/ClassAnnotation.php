<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep;
use Ep\Contract\AnnotationInterface;
use ReflectionClass;
use Reflector;

/**
 * @Annotation
 * @Target("CLASS")
 */
class ClassAnnotation implements AnnotationInterface
{
    /**
     * @param ReflectionClass $reflector
     */
    public function process(object $instance, Reflector $reflector, array $arguments = []): void
    {
    }
}
