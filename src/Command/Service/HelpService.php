<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep;
use Ep\Base\Config;
use Ep\Console\Command;
use Ep\Helper\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use Yiisoft\Injector\Injector;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;

final class HelpService extends Service
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function getAllCommands(): array
    {
        return array_merge(
            array_map([$this, 'wrapCommand'], $this->getCommandsInfoFromFiles($this->getEpCommands(), $this->getEpConfig())),
            array_map([$this, 'wrapCommand'], $this->getCommandsInfoFromFiles($this->getUserCommands(), $this->getUserConfig()))
        );
    }

    private function getEpCommands(): array
    {
        return $this->getFiles(str_replace('\\', '/', $this->aliases->get('@ep/src')), $this->config->commandDirAndSuffix);
    }

    private function getUserCommands(): array
    {
        return $this->getFiles($this->getAppPath(), $this->userCommandDirAndSuffix);
    }

    private function getFiles(string $commandPath, string $suffix): array
    {
        return array_map(static function ($path) use ($commandPath): string {
            return trim(str_replace([$commandPath, '.php'], '', $path), '/');
        }, FileHelper::findFiles($commandPath, [
            'filter' => (new PathMatcher())->only('**' . $suffix . '/*' . $suffix . '.php')
        ]));
    }

    private function wrapCommand(array $info): SymfonyCommand
    {
        return new class($this->container, $info['name'], $info['ns'], $info['class'], $info['action'], $info['actionId']) extends SymfonyCommand
        {
            private ContainerInterface $container;
            private Command $command;
            private string $action;
            private string $actionId;

            public function __construct(ContainerInterface $container, string $name, string $ns, string $class, string $action, string $actionId)
            {
                $this->command = $container->get($class);
                $this->action = $action;
                $this->actionId = $actionId;

                $ns = $ns === 'Ep' ? '' : ($ns . ':');

                parent::__construct($ns . $name);
            }

            protected function configure(): void
            {
                /** @var CommandDefinition[] $definitions */
                $definitions = $this->command->getDefinitions();
                if (array_key_exists($this->actionId, $definitions)) {
                    $this
                        ->setDefinition($definitions[$this->actionId]->getDefinition())
                        ->setDescription($definitions[$this->actionId]->getDescription())
                        ->setHelp($definitions[$this->actionId]->getHelp());
                }
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return $this->container->get(Injector::class)->invoke([$this->command, $this->action]);
            }
        };
    }

    private function getCommandsInfoFromFiles(array $files, array $config): array
    {
        foreach ($files as $name) {
            $map[$name] = array_filter(
                (new ReflectionClass($config['appNamespace'] . '\\' . str_replace('/', '\\', $name)))->getMethods(ReflectionMethod::IS_PUBLIC),
                fn (ReflectionMethod $ref) => strpos($ref->getName(), $config['actionSuffix']) !== false
            );
        }
        foreach ($map as $name => $actions) {
            foreach ($actions as $ref) {
                $action = '/' . Str::rtrim($ref->getName(), $config['actionSuffix']);
                if ($action === '/' . $config['defaultAction']) {
                    $action = '';
                }
                $commands[] = [
                    'ns' => $config['appNamespace'],
                    'class' => $ref->getDeclaringClass()->getName(),
                    'name' => $this->getCommandName($name, $action, $config),
                    'action' => $ref->getName(),
                    'actionId' => Str::rtrim($ref->getName(), $config['actionSuffix'])
                ];
            }
        }
        return $commands;
    }

    private function getCommandName(string $name, string $action, array $config): string
    {
        $prefix = trim(Str::camelToId(Str::rtrim('/' . $name, '/' . $config['commandDirAndSuffix'], false), '-', true), '/');
        $basename = Str::camelToId(basename($name, $config['commandDirAndSuffix']), '-', true);
        if ($prefix) {
            return sprintf('%s/%s%s', $prefix, $basename, $action);
        } else {
            return $basename . $action;
        }
    }
}
