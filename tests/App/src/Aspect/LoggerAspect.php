<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep;
use Ep\Contract\AnnotationInterface;
use ReflectionMethod;
use Reflector;

/**
 * @Annotation
 * @Target("METHOD")
 */
class LoggerAspect implements AnnotationInterface
{
    /**
     * @param ReflectionMethod  $reflector
     */
    public function process(object $instance, Reflector $reflector, array $arguments = []): void
    {
        Ep::getLogger()->info('i am logged');
    }
}
