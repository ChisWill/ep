<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Console\Command;
use Ep\Helper\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
            array_map([$this, 'wrapCommand'], $this->getCommandsData($this->getEpCommandFiles(), $this->getEpConfig())),
            array_map([$this, 'wrapCommand'], $this->getCommandsData($this->getUserCommandFiles(), $this->getUserConfig()))
        );
    }

    private function getEpCommandFiles(): array
    {
        return $this->getFiles(str_replace('\\', '/', $this->aliases->get('@ep/src')), $this->config->commandDirAndSuffix);
    }

    private function getUserCommandFiles(): array
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

    private function wrapCommand(CommandData $commandData): SymfonyCommand
    {
        return new class ($this->container, $commandData) extends SymfonyCommand
        {
            private ContainerInterface $container;
            private CommandData $commandData;
            private Command $command;

            public function __construct(ContainerInterface $container, CommandData $commandData)
            {
                $this->container = $container;
                $this->commandData = $commandData;
                $this->command = $container->get($commandData->className);

                if (in_array($commandData->commandName, ['help', 'list'])) {
                    $name = $commandData->commandName;
                } else {
                    $name = $commandData->appNamespace . ':' . $commandData->commandName;
                }
                parent::__construct($name);
            }

            protected function configure(): void
            {
                /** @var CommandDefinition[] $definitions */
                $definitions = $this->command->getDefinitions();
                if (array_key_exists($this->commandData->actionId, $definitions)) {
                    $this
                        ->setDefinition($definitions[$this->commandData->actionId]->getDefinition())
                        ->setDescription($definitions[$this->commandData->actionId]->getDescription())
                        ->setHelp($definitions[$this->commandData->actionId]->getHelp());
                }
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return $this->container->get(Injector::class)->invoke([$this->command, $this->commandData->action]);
            }
        };
    }

    private function getCommandsData(array $files, array $config): array
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
                $commandData = new CommandData();
                $commandData->appNamespace = $config['appNamespace'];
                $commandData->className = $ref->getDeclaringClass()->getName();
                $commandData->commandName = $this->getCommandName($name, $action, $config);
                $commandData->action = $ref->getName();
                $commandData->actionId = Str::rtrim($ref->getName(), $config['actionSuffix']);
                $commands[] = $commandData;
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

class CommandData
{
    public string $appNamespace;
    public string $className;
    public string $commandName;
    public string $action;
    public string $actionId;
}
