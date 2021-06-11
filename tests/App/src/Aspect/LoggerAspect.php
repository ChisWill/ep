<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep;
use Ep\Annotation\Service;
use Ep\Contract\AnnotationInterface;
use Reflector;
use Yiisoft\Log\Logger;

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
