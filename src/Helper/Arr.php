<?php

declare(strict_types=1);

namespace Ep\Helper;

class Arr
{
    /**
     * Usage examples,
     * 
     * ```php
     * $user = [
     *     'id' => 1,
     *     'address' => [
     *         'street' => 'home'
     *     ]
     * ];
     * $street = \Ep\Helper\Arr::getValue($user, 'address.street', 'defaultValue');
     * // $street is 'home'
     * ```
     *
     * @param  array      $array   待操作数组
     * @param  int|string $key     键名
     * @param  mixed      $default 默认值
     * 
     * @return mixed
     */
    public static function getValue(array $array, $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (is_int($key)) {
            return $default;
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : $default;
        } else {
            return $default;
        }
    }

    /**
     * 判断数组是否以数字为键
     * ps. 包括空数组
     *
     * @param  array   $array       待检查数组
     * @param  bool $consecutive 检查键是否从0开始
     * 
     * @return bool
     */
    public static function isIndexed(array $array, bool $consecutive = false): bool
    {
        if (empty($array)) {
            return true;
        }

        if ($consecutive) {
            return array_keys($array) === range(0, count($array) - 1);
        } else {
            foreach ($array as $key => $value) {
                if (!is_int($key)) {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * 删除数组一项元素并返回，如果不存在则返回给定的默认值
     *
     * Usage examples,
     *
     * ```php
     * // $array = ['type' => 'A', 'options' => [1, 2]];
     * // working with array
     * $type = \Ep\Helper\Arr::remove($array, 'type');
     * // $array content
     * // $array = ['options' => [1, 2]];
     * ```
     *
     * @param  array  $array   待操作数组
     * @param  string $key     键名
     * @param  mixed  $default 默认值
     * 
     * @return mixed
     */
    public static function remove(array &$array, string $key, $default = null)
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            unset($array[$key]);

            return $value;
        }

        return $default;
    }

    /**
     * 构建一个键值对数组
     *
     * Usage examples,
     *
     * ```php
     * $array = [
     *     ['id' => '1', 'name' => 'a', 'class' => 'x'],
     *     ['id' => '2', 'name' => 'b', 'class' => 'x'],
     *     ['id' => '3', 'name' => 'c', 'class' => 'y'],
     * ];
     *
     * $result = \Ep\Helper\Arr::map($array, 'id', 'name');
     * // the result is:
     * // [
     * //     '1' => 'a',
     * //     '2' => 'b',
     * //     '3' => 'c',
     * // ]
     *
     * $result = \Ep\Helper\Arr::map($array, 'id', 'name', 'class');
     * // the result is:
     * // [
     * //     'x' => [
     * //         '1' => 'a',
     * //         '2' => 'b',
     * //     ],
     * //     'y' => [
     * //         '3' => 'c',
     * //     ],
     * // ]
     * ```
     *
     * @param  array       $array 待操作数组
     * @param  string|int  $from  作为键的字段
     * @param  string|int  $to    作为值的字段
     * @param  string|null $group 分组字段
     * 
     * @return array
     */
    public static function map(array $array, $from, $to, ?string $group = null): array
    {
        if ($group === null) {
            return array_column($array, $to, $from);
        }

        $result = [];
        foreach ($array as $item) {
            $groupKey = static::getValue($item, $group, '');
            $key = static::getValue($item, $from, '');
            $result[$groupKey][$key] = static::getValue($item, $to);
        }

        return $result;
    }

    /**
     * 合并多个数组，相同键的标量将覆盖，相同键的数组将合并
     * 
     * @param  array $args 要合并的数组
     * 
     * @return array       合并后的数组
     */
    public static function merge(...$args): array
    {
        $result = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_int($k)) {
                    if (isset($result[$k])) {
                        $result[] = $v;
                    } else {
                        $result[$k] = $v;
                    }
                } elseif (is_array($v) && isset($result[$k]) && is_array($result[$k])) {
                    $result[$k] = static::merge($result[$k], $v);
                } else {
                    $result[$k] = $v;
                }
            }
        }

        return $result;
    }

    /**
     * 数组转 XML
     * 
     * @param  array  $array 待转换数组
     * 
     * @return string        XML
     */
    public static function toXml(array $array): string
    {
        $xml = '<xml>';
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= '<' . $key . '>' . $val . '</' . $key . '>';
            } else {
                $xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
            }
        }
        $xml .= '</xml>';
        return $xml;
    }

    /**
     * XML 转数组
     * 
     * @param  string $xml 待转换 XML 字符
     * 
     * @return array       数组
     */
    public static function fromXml(string $xml): array
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}
