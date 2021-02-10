<?php

declare(strict_types=1);

namespace Ep\Helper;

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
        if (mb_strlen($text, 'UTF-8') > $length) {
            return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
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
        $alps = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $oths = '!@#$%^&*()-_[]{}<>~`+=,.;:/?|';
        $chars = '';
        switch ($type) {
            case 'd':
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
     * @return string 签名
     */
    public static function getSign(array $params, string $secret, string $algo = 'sha256'): string
    {
        ksort($params);
        foreach ($params as $key => $value) {
            $arr[] = $key . '=' . $value;
        }
        return hash_hmac($algo, implode('&', $arr), $secret);
    }
}
