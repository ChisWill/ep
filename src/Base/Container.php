<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Kit\Annotate;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    private ContainerInterface $container;
    private Annotate $annotate;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->annotate = $container->get(Annotate::class);
    }

    private array $map = [];

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        $instance = $this->container->get($id);

        if (!isset($this->map[$id])) {
            $this->map[$id] = true;
            $this->annotate->property($instance);
        }

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return $this->container->has($id);
    }
}
