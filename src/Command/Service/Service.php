<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep;
use Ep\Base\Config;
use Ep\Console\Service as ConsoleService;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Kit\Util;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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

    protected ConsoleRequestInterface $request;

    /**
     * @return static
     */
    final public function load(ConsoleRequestInterface $request)
    {
        $new = clone $this;
        $new->request = $request;

        $new->initDefaultOptions();

        $new->configure();

        return $new;
    }

    protected string $userRootNamespace;
    protected array $defaultOptions;

    private function initDefaultOptions(): void
    {
        $options = $this->request->getOptions();

        $this->userRootNamespace = $options['common']['userRootNamespace'];
        if (!empty($options['app'])) {
            $this->defaultOptions = $options['apps'][$options['app']][$this->getId()] ?? [];
        } else {
            $this->defaultOptions = $options[$this->getId()] ?? [];
        }
    }

    protected ?ConnectionInterface $db = null;

    protected function getDb(): Connection
    {
        if ($this->db === null) {
            $db = $this->request->getOption('db') ?? $this->defaultOptions['db'] ?? $this->request->getOption('common.db') ?? null;
            try {
                $this->db = Ep::getDb($db);
            } catch (NotFoundExceptionInterface $e) {
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

    abstract protected function configure(): void;
}
