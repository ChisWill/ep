<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep;
use Ep\Base\Config;
use Ep\Console\Service as ConsoleService;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Connection\Connection;
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
    protected Aliases $aliases;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Config::class);
        $this->consoleService = $container->get(ConsoleService::class);
        $this->aliases = $container->get(Aliases::class);
    }

    protected array $options;
    protected string $userAppNamespace;
    protected string $userCommandDirAndSuffix;
    protected string $userActionSuffix;
    protected string $userDefaultAction;

    public function init(array $options): void
    {
        $this->options = $options;

        $this->userAppNamespace = $options['user.appNamespace'];
        $this->userCommandDirAndSuffix = $options['user.commandDirAndSuffix'];
        $this->userActionSuffix = $options['user.actionSuffix'];
        $this->userDefaultAction = $options['user.defaultAction'];

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

    protected ?Connection $db = null;

    protected function getDb(): Connection
    {
        if ($this->db === null) {
            $db = $this->options['db'] ?? $this->defaultOptions['db'] ?? $this->options['common']['db'] ?? null;
            try {
                $this->db = Ep::getDb($db);
            } catch (NotFoundException $e) {
                $this->invalid('db', $db);
            }
        }
        return $this->db;
    }

    protected function getEpConfig(): array
    {
        return [
            'appNamespace' => $this->config->appNamespace,
            'commandDirAndSuffix' => $this->config->commandDirAndSuffix,
            'actionSuffix' => $this->config->actionSuffix,
            'defaultAction' => $this->config->defaultAction
        ];
    }

    protected function getUserConfig(): array
    {
        return [
            'appNamespace' => $this->userAppNamespace,
            'commandDirAndSuffix' => $this->userCommandDirAndSuffix,
            'actionSuffix' => $this->userActionSuffix,
            'defaultAction' => $this->userDefaultAction
        ];
    }

    private ?string $appPath = null;

    /**
     * @throws InvalidArgumentException
     */
    public function getAppPath(): string
    {
        if ($this->appPath === null) {
            $vendorDirname = dirname($this->aliases->get($this->config->vendorPath));
            $composerPath = $vendorDirname . '/composer.json';
            if (!file_exists($composerPath)) {
                $this->throw('Unable to find composer.json in your project root.');
            }
            $content = json_decode(file_get_contents($composerPath), true);
            $autoload = ($content['autoload']['psr-4'] ?? []) + ($content['autoload-dev']['psr-4'] ?? []);
            foreach ($autoload as $ns => $path) {
                if ($ns === $this->userAppNamespace . '\\') {
                    $appPath = $path;
                    break;
                }
            }
            if (!isset($appPath)) {
                $this->throw('You should set the "autoload[psr-4]" configuration in your composer.json first.');
            }
            $this->appPath = str_replace('\\', '/', $vendorDirname . '/' . $appPath);
        }

        return $this->appPath;
    }

    protected function getClassNameByFile(string $file): string
    {
        return str_replace([$this->getAppPath(), '.php', '/'], [$this->userAppNamespace, '', '\\'], $file);
    }

    protected function findClassFiles(string $path, array $exceptPatterns = []): array
    {
        return FileHelper::findFiles($path, [
            'filter' => (new PathMatcher())->only('**.php')->except(...$exceptPatterns)
        ]);
    }

    protected function required(string $option): void
    {
        $this->throw("The \"{$option}\" option is required.");
    }

    protected function invalid(string $option, string $value): void
    {
        $this->throw("The value \"{$value}\" of the option \"{$option}\" is invalid.");
    }

    protected function throw(string $message): void
    {
        throw new InvalidArgumentException($message);
    }

    abstract protected function getId(): string;
}
