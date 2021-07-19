<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Kit\Annotate;
use Yiisoft\Di\Container as YiiContainer;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    private ContainerInterface $rootContainer;
    private Annotate $annotate;

    public function __construct(ContainerInterface $rootContainer, Injector $injector)
    {
        $this->rootContainer = $rootContainer;
        $this->annotate = $injector->make(Annotate::class, [$this]);
    }

    private array $definitions = [];
    private ?ContainerInterface $container = null;

    public function set(array $definitions): void
    {
        $this->definitions = $definitions + $this->definitions;
        $this->container = new YiiContainer($definitions, [], [], $this->rootContainer);
    }

    private array $map = [];

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (array_key_exists($id, $this->definitions)) {
            $instance = $this->container->get($id);
            $key = 'new-' . $id;
        } else {
            $instance = $this->rootContainer->get($id);
            $key = 'root-' . $id;
        }

        if (!isset($this->map[$key])) {
            $this->map[$key] = true;
            $this->annotate->property($instance);
        }

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return $this->rootContainer->has($id) || $this->container && $this->container->has($id);
    }

    /**
     * @param string|array $ids
     */
    public function clear($ids): void
    {
        foreach ((array) $ids as $id) {
            unset($this->definitions[$id]);
            unset($this->map['new-' . $id]);
        }
    }
}
