<?php

declare(strict_types=1);

namespace Ep\Contract;

interface InjectorInterface
{
    /**
     * @return mixed
     */
    public function call(object $instance, string $method, array $arguments = []);

    /**
     * @return mixed
     */
    public function invoke(callable $callable, array $arguments = []);

    /**
     * @return mixed
     */
    public function make(string $class, array $arguments = []);
}
