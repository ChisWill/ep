<?php

declare(strict_types=1);

namespace Ep\Contract;

interface InjectorInterface
{
    /**
     * @return mixed
     */
    public function call(object $object, string $method, array $arguments = []);

    /**
     * @return mixed
     */
    public function invoke(callable $callable, array $arguments = []);

    public function make(string $class, array $arguments = []): object;
}
