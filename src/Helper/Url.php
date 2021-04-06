<?php

declare(strict_types=1);

namespace Ep\Helper;

use Ep;

class Url
{
    /**
     * 根据已有地址，增加额外参数，如果已存在则覆盖
     *
     * @param  string  $url    基础网址
     * @param  array   $params URL参数
     * @param  string  $sign   签名参数名，为空表示不设置
     * 
     * @return string
     */
    public static function addParams(string $url, array $params = [], string $sign = ''): string
    {
        $urlInfo = parse_url($url);
        if (isset($urlInfo['query'])) {
            parse_str($urlInfo['query'], $queryParams);
        } else {
            $queryParams = [];
        }
        $params += $queryParams;
        if ($sign !== '') {
            $params[$sign] = Str::getSign($params, Ep::getConfig()->secretKey, 'md5');
        }
        $baseUrl = $queryParams ? substr($url, 0, strpos($url, '?')) : $url;
        $d = $params ? '?' : '';
        return $baseUrl . $d . http_build_query($params);
    }

    /**
     * 检查 URL 地址是否被篡改过
     * 
     * @param  string $sign   签名字段名
     * @param  array  $params 待检查参数
     * 
     * @return bool
     */
    public static function checkSign($sign, array $params = [])
    {
        $old = Arr::remove($params, $sign, '');
        $new = Str::getSign($params, Ep::getConfig()->secretKey, 'md5');
        return $old === $new;
    }

    /**
     * URL安全的 base64 编码
     * 
     * @param  string $string
     * 
     * @return string
     */
    public static function base64encode(string $input): string
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    /**
     * URL安全的 base64 解码
     * 
     * @param  string $input
     * 
     * @return string
     */
    public static function base64decode(string $input): string
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
