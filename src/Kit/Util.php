<?php

declare(strict_types=1);

namespace Ep\Kit;

use Ep\Base\Config;
use Yiisoft\Aliases\Aliases;
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
                $vendorPath = dirname($this->aliases->get($this->config->vendorPath));
                $composerPath = $vendorPath . '/composer.json';
                if (!file_exists($composerPath)) {
                    throw new InvalidArgumentException('Unable to find composer.json in your project root.');
                }
                $content = json_decode(file_get_contents($composerPath), true);
                $autoload = ($content['autoload']['psr-4'] ?? []) + ($content['autoload-dev']['psr-4'] ?? []);
                foreach ($autoload as $ns => $path) {
                    if ($ns === $rootNamespace . '\\') {
                        $appPath = $path;
                        break;
                    }
                }
                if (!isset($appPath)) {
                    throw new InvalidArgumentException('You should set the "autoload[psr-4]" configuration in your composer.json first.');
                }
                $this->appPath[$rootNamespace] = str_replace('\\', '/', $vendorPath . '/' . $appPath);
            }
        }

        return $this->appPath[$rootNamespace];
    }
}
