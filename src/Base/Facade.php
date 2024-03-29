<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep;
use InvalidArgumentException;

abstract class Facade
{
    private static array $instances = [];

    /**
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $instance = self::getInstance();

        return $instance->$name(...$arguments);
    }

    public static function swap(object $instance): void
    {
        self::$instances[static::getFacadeAccessor()] = $instance;
    }

    public static function clear(): void
    {
        unset(self::$instances[static::getFacadeAccessor()]);
    }

    protected static function getFacadeAccessor(): string
    {
        throw new InvalidArgumentException(sprintf('%s does not implement method %s().', static::class, __FUNCTION__));
    }

    private static function getInstance(): object
    {
        $id = static::getFacadeAccessor();

        if (isset(self::$instances[$id])) {
            return self::$instances[$id];
        }

        return self::$instances[$id] = Ep::getDi()->get($id);
    }
}
