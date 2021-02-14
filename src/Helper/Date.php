<?php

declare(strict_types=1);

namespace Ep\Helper;

class Date
{
    /**
     * 获得标准格式化时间
     * 
     * @param  int|null $timestamp 时间戳，不传默认为当前时间
     * 
     * @return string
     */
    public static function fromUnix(?int $timestamp = null): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        return date('Y-m-d H:i:s', $timestamp);
    }
}
