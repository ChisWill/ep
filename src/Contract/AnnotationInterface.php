<?php

declare(strict_types=1);

namespace Ep\Contract;

use Reflector;

interface AnnotationInterface
{
    public const TYPE_PROPERTY = 'property';
    public const TYPE_METHOD = 'method';
    public const TYPE_CLASS = 'class';

    /**
     * @return mixed
     */
    public function process(object $instance, Reflector $reflector, array $arguments = []);
}
