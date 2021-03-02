<?php

/**
 * 徒手调试专用
 */
function tes(...$args): void
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'test';
    $func = strpos($caller, 'dum') === false ? 'print_r' : 'var_dump';
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
function test(...$args): void
{
    call_user_func_array('tes', $args);
    exit;
}

/**
 * @see tes()
 */
function dum(...$args): void
{
    call_user_func_array('tes', $args);
}

/**
 * @see tes()
 */
function dump(...$args): void
{
    call_user_func_array('tes', $args);
    exit;
}
