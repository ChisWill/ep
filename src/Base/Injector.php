<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\InjectorInterface;
use Ep\Kit\Annotate;
use Yiisoft\Injector\Injector as YiiInjector;
use Psr\Container\ContainerInterface;

final class Injector implements InjectorInterface
{
    private YiiInjector $injector;
    private Annotate $annotate;

    public function __construct(
        ContainerInterface $container,
        Annotate $annotate
    ) {
        $this->injector = new YiiInjector($container);
        $this->annotate = $annotate;
    }

    /**
     * {@inheritDoc}
     */
    public function call(object $instance, string $method, array $arguments = [])
    {
        return $this->annotate->method($instance, $method, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function invoke(callable $callable, array $arguments = [])
    {
        return $this->injector->invoke($callable, $arguments);
    }

    /**
     * {@inheritDoc}
     */
    public function make(string $class, array $arguments = [])
    {
        $instance = $this->injector->make($class, $arguments);

        $this->annotate->property($instance, $arguments);

        return $instance;
    }
}
