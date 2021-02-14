<?php

declare(strict_types=1);

namespace Ep\Helper;

use InvalidArgumentException;

class Alias
{
    protected static array $aliases = [];

    /**
     * 设置别名
     * 
     * @param string $alias 别名
     * @param string $path  路径或别名
     */
    public static function set(string $alias, string $path): void
    {
        if (!static::isAlias($alias)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        $path = rtrim($path, '\\/');
        if (!array_key_exists($root, static::$aliases)) {
            if ($pos === false) {
                static::$aliases[$root] = $path;
            } else {
                static::$aliases[$root] = [$alias => $path];
            }
        } elseif (is_string(static::$aliases[$root])) {
            if ($pos === false) {
                static::$aliases[$root] = $path;
            } else {
                static::$aliases[$root] = [
                    $alias => $path,
                    $root => static::$aliases[$root],
                ];
            }
        } else {
            static::$aliases[$root][$alias] = $path;
            krsort(static::$aliases[$root]);
        }
    }

    /**
     * 根据别名获取路径
     * 
     * @param  string $alias 别名
     * 
     * @return string        路径
     * @throws InvalidArgumentException
     */
    public static function get(string $alias): string
    {
        if (!static::isAlias($alias)) {
            return $alias;
        }

        $foundAlias = static::findAlias($alias);

        if ($foundAlias === null) {
            throw new InvalidArgumentException("Invalid path alias: $alias");
        }

        $foundSubAlias = static::findAlias($foundAlias);
        if ($foundSubAlias === null) {
            return $foundAlias;
        }

        return static::get($foundSubAlias);
    }

    /**
     * 删除别名
     * 
     * @param string $alias 别名
     */
    public static function remove(string $alias): void
    {
        if (!static::isAlias($alias)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (array_key_exists($root, static::$aliases)) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }

    protected static function findAlias(string $alias): ?string
    {
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (array_key_exists($root, static::$aliases)) {
            if (\is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            }

            foreach (static::$aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return $path . substr($alias, strlen($name));
                }
            }
        }

        return null;
    }

    /**
     * 判断是否是别名
     * 
     * @param  string $alias 别名
     * 
     * @return bool
     */
    public static function isAlias(string $alias): bool
    {
        return !strncmp($alias, '@', 1);
    }
}
