<?php

declare(strict_types=1);

namespace Ep\Helper;

/**
 * 字符串操作助手类
 *
 * @author ChisWill
 */
class Str
{
    /**
     * 长文本截取后缩略显示
     * 
     * @param  string $text   原文本
     * @param  int    $length 显示长度
     * @param  string $suffix 省略后缀
     * @return string         处理后文本
     */
    public static function subtext(string $text, int $length, string $suffix = '...'): string
    {
        if (mb_strlen($text, 'utf8') > $length) {
            return mb_substr($text, 0, $length, 'utf8') . $suffix;
        } else {
            return $text;
        }
    }

    /**
     * 生成指定位数的随机码
     * 
     * @param  integer $length 随机码的长度
     * @param  string  $type   随机码的类型
     * @return string          生成后的随机码
     */
    public static function random(int $length = 16, string $type = 'w'): string
    {
        $nums = '0123456789';
        $alps = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $oths = '!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
        $chars = '';
        switch ($type) {
            case 'n':
                $chars = $nums;
                break;
            case 'a':
                $chars = $alps;
                break;
            case 'o':
                $chars = $oths;
                break;
            case 'w':
                $chars = $alps .= $nums;
                break;
            default:
                $chars = $alps .= $nums .= $oths;
                break;
        }
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $string;
    }

    /**
     * 生成标准签名
     * 
     * @param  array  参数
     * @param  string 秘钥
     * @return string 签名
     */
    public static function genSign(array $params, string $secret): string
    {
        ksort($params);

        $tmp = [];
        foreach ($params as $key => $value) {
            $tmp[] = $key . '=' . $value;
        }
        $str = implode('&', $tmp);
        return hash_hmac('SHA256', $str, $secret);
    }
}
