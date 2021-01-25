<?php

declare(strict_types=1);

namespace Ep\Helper;

/**
 * 时间、日期操作助手类
 *
 * @author ChisWill
 */
class Date
{
    /**
     * 获得标准格式时间
     * 
     * @param  int|null $timestamp 时间戳，默认当前时间
     * @return string              格式化后时间
     */
    public static function fromUnix(?int $timestamp = null): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        return date('Y-m-d H:i:s', $timestamp);
    }
}
