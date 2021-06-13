<?php

declare(strict_types=1);

namespace Ep\Command\Service;

use Ep\Console\Command;
use Ep\Helper\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
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

    private function wrapCommand(CommandDataSet $data): SymfonyCommand
    {
        return new class ($data) extends SymfonyCommand
        {
            private CommandDataSet $data;

            public function __construct(CommandDataSet $data)
            {
                $this->data = $data;

                if (in_array($data->commandName, ['help', 'list'])) {
                    $name = $data->commandName;
                } else {
                    $name = $data->appNamespace . ':' . $data->commandName;
                }
                parent::__construct($name);
            }

            protected function configure(): void
            {
                /** @var CommandDefinition[] $definitions */
                $definitions = $this->data->command->getDefinitions();
                if (array_key_exists($this->data->actionId, $definitions)) {
                    $this
                        ->setDefinition($definitions[$this->data->actionId]->getDefinition())
                        ->setDescription($definitions[$this->data->actionId]->getDescription())
                        ->setHelp($definitions[$this->data->actionId]->getHelp());
                }
            }
        };
    }

    private function getCommandsData(array $files, array $config): array
    {
        foreach ($files as $name) {
            $map[$name] = array_filter(
                (new ReflectionClass($config['appNamespace'] . '\\' . str_replace('/', '\\', $name)))->getMethods(ReflectionMethod::IS_PUBLIC),
                fn (ReflectionMethod $ref): bool => strpos($ref->getName(), $config['actionSuffix']) !== false
            );
        }
        foreach ($map as $name => $actions) {
            foreach ($actions as $ref) {
                $action = '/' . Str::rtrim($ref->getName(), $config['actionSuffix']);
                if ($action === '/' . $config['defaultAction']) {
                    $action = '';
                }
                $dataSet = new CommandDataSet();
                $dataSet->command = $this->container->get($ref->getDeclaringClass()->getName());
                $dataSet->appNamespace = $config['appNamespace'];
                $dataSet->commandName = $this->getCommandName($name, $action, $config);
                $dataSet->actionId = Str::rtrim($ref->getName(), $config['actionSuffix']);
                $commands[] = $dataSet;
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

class CommandDataSet
{
    public Command $command;
    public string $appNamespace;
    public string $commandName;
    public string $actionId;
}
