<?php

namespace Ep\helper;

/**
 * 字符串操作助手类
 *
 * @author ChisWill
 */
class Str
{
    public static function subtext($text, $length, $suffix = '...')
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
    public static function random($length = 16, $type = 'w')
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
     * 生成签名
     */
    public static function genSign($params, $secret)
    {
        ksort($params);

        $tmp = [];
        foreach ($params as $key => $value) {
            $tmp[] = $key . '=' . $value;
        }
        $str = implode('&', $tmp);
        return hash_hmac('SHA256', $str, $secret);;
    }
}
