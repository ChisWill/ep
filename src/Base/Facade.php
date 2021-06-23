<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use LogicException;

abstract class Facade
{
    protected static array $instances = [];

    public static function __callStatic($name, $arguments)
    {
        $instance = static::getInstance();

        return $instance->$name(...$arguments);
    }

    public static function swap(object $instance): void
    {
        static::$instances[static::getFacadeAccessor()] = $instance;
    }

    public static function clear(): void
    {
        unset(static::$instances[static::getFacadeAccessor()]);
    }

    protected static function getFacadeAccessor(): string
    {
        throw new LogicException(sprintf('%s does not implement method %s().', static::class, __FUNCTION__));
    }

    private static function getInstance(): object
    {
        $id = static::getFacadeAccessor();

        if (isset(static::$instances[$id])) {
            return static::$instances[$id];
        }

        return static::$instances[$id] = Ep::getDi()->get($id);
    }
}
