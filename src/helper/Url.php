<?php

declare(strict_types=1);

namespace Ep\Helper;

use Ep;

class Url
{
    public static $signName = '_sign';

    /**
     * 根据已有地址，增加额外参数
     *
     * @param  string  $url    基础网址
     * @param  array   $params URL参数
     * @param  boolean $sign   是否添加h签名参数
     * @return string
     */
    public static function addParams($url, $params = [], $sign = false)
    {
        if ($sign === true) {
            $params[self::$signName] = self::getSign($params);
        }
        $info = parse_url($url);
        $url = explode('?', $url)[0];
        if (isset($info['query'])) {
            $d = $params ? '&' : '';
            return $url . '?' . $info['query'] . $d . http_build_query($params);
        } else {
            $d = $params ? '?' : '';
            return $url . $d . http_build_query($params);
        }
    }

    /**
     * 检查 URL 地址是否被篡改过
     * 
     * @param  array   $get GET 参数
     * @return boolean
     */
    public static function checkSign($get = [])
    {
        $old = $get[self::$signName] ?? '';
        unset($get[self::$signName]);
        $new = self::getSign($get);
        return $old === $new;
    }

    private static function getSign($params)
    {
        $values = array_merge(array_values($params), [Ep::getConfig()->secretKey]);
        return substr(md5(implode('', $values)), 3, 13);
    }

    /**
     * URL安全的 base64 编码
     * 
     * @param  string $string
     * @return string
     */
    public static function base64encode($string)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

    /**
     * URL安全的 base64 解码
     * 
     * @param  string $string
     * @return string
     */
    public static function base64decode($string)
    {
        $string = str_replace(['-', '_'], ['+', '/'], $string);
        $mod4 = strlen($string) % 4;
        if ($mod4) {
            $string .= substr('====', $mod4);
        }
        return base64_decode($string);
    }
}
