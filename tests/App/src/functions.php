<?php

declare(strict_types=1);

if (!function_exists('import')) {
    function import(string $name, string $ext = '.php'): array
    {
        $dir = dirname(__DIR__) . '/config';

        $configPath = $dir . '/' . $name . $ext;
        $localConfigPath = $dir . '/' . $name . '-local' . $ext;

        if (file_exists($localConfigPath)) {
            return array_merge(require($configPath), require($localConfigPath));
        } else {
            return require($configPath);
        }
    }
}
