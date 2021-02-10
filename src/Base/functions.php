<?php

/**
 * 徒手调试专用，可以传入任意个数变量，便于查看
 */
function tes(...$args)
{
    $caller = debug_backtrace()[1]['function'] ?? 'test';
    $func = $caller == 'test' ? 'print_r' : 'var_dump';
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
function dum(...$args)
{
    call_user_func_array('tes', $args);
}

/**
 * @see tes()
 */
function dump(...$args)
{
    call_user_func_array('tes', $args);
    exit;
}
