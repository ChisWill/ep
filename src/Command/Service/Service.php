<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep;
use Ep\Base\Config;
use Ep\Console\Service as ConsoleService;
use Ep\Kit\Util;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Factory\Exception\NotFoundException;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use Psr\Container\ContainerInterface;
use InvalidArgumentException;

abstract class Service
{
    protected ContainerInterface $container;
    protected Config $config;
    protected ConsoleService $consoleService;
    protected Util $util;
    protected Aliases $aliases;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Config::class);
        $this->consoleService = $container->get(ConsoleService::class);
        $this->util = $container->get(Util::class);
        $this->aliases = $container->get(Aliases::class);
    }

    protected array $options;
    protected string $userRootNamespace;

    public function initialize(array $options): void
    {
        $this->options = $options;
        $this->userRootNamespace = $options['common']['userRootNamespace'];

        $this->initDefaultOptions();
    }

    protected array $defaultOptions;

    private function initDefaultOptions(): void
    {
        if (!empty($this->options['app'])) {
            $this->defaultOptions = $this->options['apps'][$this->options['app']][$this->getId()] ?? [];
        } else {
            $this->defaultOptions = $this->options[$this->getId()] ?? [];
        }
    }

    protected ?ConnectionInterface $db = null;

    protected function getDb(): Connection
    {
        if ($this->db === null) {
            $db = $this->options['db'] ?? $this->defaultOptions['db'] ?? $this->options['common']['db'] ?? null;
            try {
                $this->db = Ep::getDb($db);
            } catch (NotFoundException $e) {
                $this->error(sprintf('The db "%s" is invalid.', $db));
            }
        }
        return $this->db;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function getAppPath(): string
    {
        return $this->util->getAppPath($this->userRootNamespace);
    }

    protected function getClassNameByFile(string $file): string
    {
        return str_replace([$this->getAppPath(), '.php', '/'], [$this->userRootNamespace, '', '\\'], $file);
    }

    protected function findClassFiles(string $path, array $exceptPatterns = []): array
    {
        return FileHelper::findFiles($path, [
            'filter' => (new PathMatcher())->only('**.php')->except(...$exceptPatterns)
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function error(string $message): void
    {
        throw new InvalidArgumentException($message);
    }

    private function getId(): string
    {
        return lcfirst(basename(str_replace('\\', '/', static::class), 'Service'));
    }
}
