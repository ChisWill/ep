<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Config;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use InvalidArgumentException;

final class Util
{
    private Config $config;
    private Aliases $aliases;

    public function __construct(Config $config, Aliases $aliases)
    {
        $this->config = $config;
        $this->aliases = $aliases;
    }

    public function getClassList(string $rootNamespace, array $exceptPatterns = []): array
    {
        $result = [];
        foreach ($this->findClassFiles($this->getAppPath($rootNamespace), $exceptPatterns) as $file) {
            $result[] = $this->getClassNameByFile($rootNamespace, $file);
        }
        return $result;
    }

    private array $appPath = [];

    /**
     * @throws InvalidArgumentException
     */
    public function getAppPath(string $rootNamespace = null): string
    {
        $rootNamespace ??= $this->config->rootNamespace;
        if (!isset($this->appPath[$rootNamespace])) {
            if ($rootNamespace === 'Ep') {
                $this->appPath[$rootNamespace] = $this->aliases->get('@ep/src');
            } else {
                $this->appPath[$rootNamespace] = $this->getAppPathByComposer($rootNamespace);
            }
        }

        return $this->appPath[$rootNamespace];
    }

    private function getAppPathByComposer(string $rootNamespace): string
    {
        $vendorPath = dirname($this->aliases->get($this->config->vendorPath));
        $composerPath = $vendorPath . '/composer.json';
        if (!file_exists($composerPath)) {
            throw new InvalidArgumentException('Unable to find composer.json in your project root.');
        }
        $content = json_decode(file_get_contents($composerPath), true);
        $autoload = ($content['autoload']['psr-4'] ?? []) + ($content['autoload-dev']['psr-4'] ?? []);
        foreach ($autoload as $ns => $path) {
            if ($ns === $rootNamespace . '\\') {
                $psrPath = $path;
                break;
            }
        }
        if (!isset($psrPath)) {
            throw new InvalidArgumentException('You should set the "autoload[psr-4]" configuration in your composer.json first.');
        }
        return str_replace('\\', '/', $vendorPath . '/' . $psrPath);
    }

    public function getClassNameByFile(string $rootNamespace, string $file): string
    {
        return str_replace([$this->getAppPath($rootNamespace), '.php', '/'], [$rootNamespace, '', '\\'], $file);
    }

    public function findClassFiles(string $path, array $exceptPatterns = []): array
    {
        return FileHelper::findFiles($path, [
            'filter' => (new PathMatcher())->only('**.php')->except(...$exceptPatterns)
        ]);
    }
}
