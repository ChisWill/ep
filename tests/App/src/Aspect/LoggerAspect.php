<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep;
use Ep\Contract\AnnotationInterface;
use Reflector;

/**
 * @Annotation
 * @Target("METHOD")
 */
class LoggerAspect implements AnnotationInterface
{
    public function process(object $instance, Reflector $reflector, array $arguments = [])
    {
        Ep::getLogger()->info('i am logged');
    }
}
