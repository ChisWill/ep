<?php

declare(strict_types=1);

namespace Ep\Helper;

use RuntimeException;

class File
{
    /**
     * 获取指定目录下的所有文件夹（不递归获取）
     * 
     * @param  string $dir 文件路径
     * 
     * @return array
     * @throws RuntimeException
     */
    public static function getDirs(string $dir): array
    {
        $handle = opendir($dir);
        if ($handle === false) {
            throw new RuntimeException("Unable to open directory: $dir");
        }
        $list = [];
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $filepath = $dir . '/' . $file;
            if (is_dir($filepath)) {
                $list[] = $filepath;
            }
        }
        closedir($handle);

        return $list;
    }

    /**
     * 递归创建文件夹，并设置权限
     *
     * @param  string   $path      文件夹位置
     * @param  integer  $mode      权限
     * @param  bool     $recursive 是否递归
     * 
     * @return bool
     * @throws RuntimeException
     */
    public static function mkdir(string $path, int $mode = 0775, bool $recursive = true): bool
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            static::mkdir($parentDir, $mode, true);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (RuntimeException $e) {
            if (!is_dir($path)) {
                throw new RuntimeException("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Failed to change permissions for directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 删除文件夹
     *
     * @param  string $dir           文件夹位置
     * @param  bool   $ignoreSymlink 是否忽略符号连接指向的内容
     *
     * @throws RuntimeException
     */
    public static function rmdir(string $dir, bool $ignoreSymlink = true): void
    {
        if (!is_dir($dir)) {
            return;
        }
        if ($ignoreSymlink === false || !is_link($dir)) {
            if (!($handle = opendir($dir))) {
                return;
            }
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    static::rmdir($path, $ignoreSymlink);
                } else {
                    try {
                        unlink($path);
                    } catch (RuntimeException $e) {
                        if (DIRECTORY_SEPARATOR === '\\') {
                            // last resort measure for Windows
                            $lines = [];
                            exec("DEL /F/Q \"$path\"", $lines, $deleteError);
                        } else {
                            throw $e;
                        }
                    }
                }
            }
            closedir($handle);
        }
        if (is_link($dir)) {
            unlink($dir);
        } else {
            rmdir($dir);
        }
    }
}
