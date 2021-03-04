<?php

declare(strict_types=1);

namespace Ep\Helper;

class System
{
    /**
     * 获取调用者名字
     * 
     * @param  string $prefix 需要去除的前缀
     * @param  string $suffix 需要去除的后缀
     * 
     * @return string
     */
    public static function getCallerName(string $prefix = '', string $suffix = ''): string
    {
        $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'] ?? '';
        preg_match("/^{$prefix}(\w+){$suffix}$/", $method, $match);
        return $match[1] ?? '';
    }
}
