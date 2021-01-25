<?php

namespace Ep\helper;

/**
 * 时间、日期操作助手类
 *
 * @author ChisWill
 */
class Date
{
    public static function now($timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        return date('Y-m-d H:i:s', $timestamp);
    }
}
