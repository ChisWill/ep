<?php

declare(strict_types=1);

namespace Ep\Helper;

use Exception;

class File
{
    /**
     * 获取指定目录下的所有文件夹（不递归获取）
     * 
     * @param  string $dir 文件路径
     * @return array       包含文件夹的数组
     */
    public static function getDirs(string $dir): array
    {
        $handle = opendir($dir);
        if ($handle === false) {
            throw new Exception("Unable to open directory: $dir");
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
     * Creates a new directory.
     *
     * This method is similar to the PHP `mkdir()` function except that
     * it uses `chmod()` to set the permission of the created directory
     * in order to avoid the impact of the `umask` setting.
     *
     * @param string $path path of the directory to be created.
     * @param integer $mode the permission to be set for the created directory.
     * @param boolean $recursive whether to create parent directories if they do not exist.
     * @return boolean whether the directory is created successfully
     * @throws Exception if the directory could not be created (i.e. php error due to parallel changes)
     */
    protected static function mkdir($path, $mode = 0775, $recursive = true)
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
        } catch (Exception $e) {
            if (!is_dir($path)) {
                throw new Exception("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (Exception $e) {
            throw new Exception("Failed to change permissions for directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Removes a directory (and all its content) recursively.
     *
     * @param string $dir the directory to be deleted recursively.
     * @param array $options options for directory remove. Valid options are:
     *
     * - traverseSymlinks: boolean, whether symlinks to the directories should be traversed too.
     *   Defaults to `false`, meaning the content of the symlinked directory would not be deleted.
     *   Only symlink would be removed in that default case.
     *
     * @throws Exception in case of failure
     */
    public static function rmdir(string $dir, array $options = []): void
    {
        if (!is_dir($dir)) {
            return;
        }
        if (isset($options['traverseSymlinks']) && $options['traverseSymlinks'] || !is_link($dir)) {
            if (!($handle = opendir($dir))) {
                return;
            }
            while (($file = readdir($handle)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    static::rmdir($path, $options);
                } else {
                    try {
                        unlink($path);
                    } catch (Exception $e) {
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
