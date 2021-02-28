<?php

declare(strict_types=1);

namespace Ep\Helper;

class Str
{
    /**
     * 移除非字母数字的字符，并转为大驼峰命名形式
     * 
     * @param  string $input     待转换字符
     * @param  string $separator 分隔符
     * 
     * @return string
     */
    public static function toPascalCase(string $input, string $separator = '_'): string
    {
        return str_replace(' ', '', ucwords(implode(' ', explode($separator, preg_replace('/[^\pL\pN]+/u', ' ', $input)))));
    }

    /**
     * 驼峰命名形式转为指定分隔符连接形式，非字母数字的字符都会转为分隔符
     * 
     * @param  string $input     待转换字符
     * @param  string $separator 分隔符
     * 
     * @return string
     */
    public static function camelToId(string $input, string $separator = '_', bool $strict = false): string
    {
        return mb_strtolower(trim(preg_replace('/[^\pL\pN]+/u', $separator, preg_replace($strict ? '/[A-Z]/' : '/(?<![A-Z])[A-Z]/', addslashes($separator) . '\0', $input)), $separator));
    }

    /**
     * 长文本截取后缩略显示
     * 
     * @param  string $text   原文本
     * @param  int    $length 显示长度
     * @param  string $suffix 省略后缀
     * 
     * @return string
     */
    public static function subtext(string $text, int $length, string $suffix = '...'): string
    {
        if (mb_strlen($text, 'UTF-8') > $length) {
            return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
        } else {
            return $text;
        }
    }

    /**
     * 生成指定位数的随机字符串
     * 
     * @param  integer $length 长度
     * @param  string  $type   类型
     * 
     * @return string
     */
    public static function random(int $length = 16, string $type = ''): string
    {
        $nums = '0123456789';
        $alps = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $chars = '';
        switch ($type) {
            case 'd':
                $chars = $nums;
                break;
            case 'a':
                $chars = $alps;
                break;
            default:
                $chars = $alps .= $nums;
                break;
        }
        $len = strlen($chars);
        if ($len < $length) {
            $chars = str_repeat($chars, intval(ceil($length / $len)));
        }
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * 生成标准签名
     * 
     * @param  array  参数
     * @param  string 秘钥
     * 
     * @return string
     */
    public static function getSign(array $params, string $secret, string $algo = 'sha256'): string
    {
        ksort($params);
        $arr = [];
        foreach ($params as $key => $value) {
            $arr[] = $key . '=' . $value;
        }
        return hash_hmac($algo, implode('&', $arr), $secret);
    }
}
