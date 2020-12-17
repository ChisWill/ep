<?php

/**
 * 徒手调试专用，可以传入任意个变量，使用、查看便捷
 */
function tes(...$args)
{
    $caller = debug_backtrace()[1]['function'] ?? 'test';
    $func = $caller == 'dump' ? 'var_dump' : 'print_r';
    $isCli = PHP_SAPI === 'cli';
    if (!$isCli && !in_array('Content-type:text/html;charset=utf-8', headers_list())) {
        header('Content-type:text/html;charset=utf-8');
    }
    foreach ($args as $msg) {
        if ($isCli) {
            $func($msg);
            echo PHP_EOL;
        } else {
            if ($func === 'var_dump') {
                $func($msg);
            } else {
                echo '<xmp>';
                $func($msg);
                echo '</xmp>';
            }
        }
    }
}

/**
 * @see tes()
 */
function test(...$args)
{
    call_user_func_array('tes', $args);
    exit;
}

/**
 * @see tes()
 */
function dump(...$args)
{
    call_user_func_array('tes', $args);
    exit;
}

/**
 * 判断是否是质数
 * 
 * @param  int  $number
 * @return bool
 */
function isPrime($number)
{
    if (!is_int($number) || $number < 2) {
        return false;
    }
    if ($number == 2 || $number == 3) {
        return true;
    }
    if ($number % 6 != 1 && $number % 6 != 5) {
        return false;
    }
    $sqrt = sqrt($number);
    for ($i = 5; $i <= $sqrt; $i += 6) {
        if ($number % $i == 0 || $number % ($i + 2) == 0) {
            return false;
        }
    }
    return true;
}
