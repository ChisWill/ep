<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep;
use Ep\Base\Config;
use Ep\Console\Service as ConsoleService;
use Ep\Contract\ConsoleRequestInterface;
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
    protected ConsoleRequestInterface $request;
    protected ConsoleService $consoleService;
    protected Aliases $aliases;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Config::class);
        $this->request = $container->get(ConsoleRequestInterface::class);
        $this->consoleService = $container->get(ConsoleService::class);
        $this->aliases = $container->get(Aliases::class);

        $this->init();
    }

    protected Connection $db;
    protected string $userAppNamespace;
    protected string $userCommandDirAndSuffix;
    protected string $userActionSuffix;
    protected string $userDefaultAction;

    private function init(): void
    {
        $options = $this->request->getOptions();
        $this->userAppNamespace = $options['user.appNamespace'];
        $this->userCommandDirAndSuffix = $options['user.commandDirAndSuffix'];
        $this->userActionSuffix = $options['user.actionSuffix'];
        $this->userDefaultAction = $options['user.defaultAction'];

        $db = $options['db'] ?? $options['common.db'] ?? null;
        try {
            $this->db = Ep::getDb($db);
        } catch (NotFoundException $e) {
            $this->invalid('db', $db);
        }
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
            $appPath = null;
            foreach ($autoload as $ns => $path) {
                if ($ns === $this->userAppNamespace . '\\') {
                    $appPath = $path;
                    break;
                }
            }
            if ($appPath === null) {
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

    protected function findClassFiles(string $path): array
    {
        return FileHelper::findFiles($path, [
            'filter' => (new PathMatcher())->only('**.php')
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
}
