<?php

declare(strict_types=1);

namespace Ep\Contract;

use Reflector;

interface AnnotationInterface
{
    /**
     * @return mixed
     */
    public function process(object $instance, Reflector $reflector, array $arguments = []);
}
