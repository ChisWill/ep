<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep\Base\Config;
use Ep\Base\ControllerLoader;
use Ep\Base\Route;
use Ep\Contract\InjectorInterface;
use Ep\Contract\NotFoundException;
use Ep\Helper\Str;
use Ep\Kit\Util;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;
use LogicException;
use ReflectionClass;
use ReflectionMethod;

final class CommandLoader implements CommandLoaderInterface
{
    private Config $config;
    private Route $route;
    private ControllerRunner $controllerRunner;
    private Factory $factory;
    private Util $util;
    private ControllerLoader $controllerLoader;

    public function __construct(
        Config $config,
        Route $route,
        ControllerRunner $controllerRunner,
        Factory $factory,
        InjectorInterface $injector,
        Util $util
    ) {
        $this->config = $config;
        $this->route = $route;
        $this->controllerRunner = $controllerRunner;
        $this->factory = $factory;
        $this->util = $util;

        $this->controllerLoader = $injector->make(ControllerLoader::class, [
            'suffix' => $this->controllerRunner->getControllerSuffix()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name)
    {
        return $this->wrapCommand($name);
    }

    private function wrapCommand(string $name): SymfonyCommand
    {
        $commandName = $this->parse($name);
        return new class ($this->controllerLoader->parse($commandName), $this->controllerRunner, $this->factory, $commandName, $name) extends SymfonyCommand
        {
            private ControllerLoader $loader;
            private ControllerRunner $runner;
            private Factory $factory;

            public function __construct(
                ControllerLoader $loader,
                ControllerRunner $runner,
                Factory $factory,
                string $name,
                string $alias
            ) {
                $this->loader = $loader;
                $this->runner = $runner;
                $this->factory = $factory;

                if ($name !== $alias) {
                    $this->setAliases([$alias]);
                }

                parent::__construct($name);
            }

            /**
             * {@inheritdoc}
             */
            protected function configure(): void
            {
                /** @var Command */
                $command = $this->loader->getController();
                $definitions = $command->getDefinitions();
                if (isset($definitions[$command->actionId])) {
                    $this
                        ->setDefinition($definitions[$command->actionId]->getDefinitions())
                        ->setDescription($definitions[$command->actionId]->getDescription())
                        ->setHelp($definitions[$command->actionId]->getHelp());
                    foreach ($definitions[$command->actionId]->getUsages() as $usage) {
                        $this->addUsage($usage);
                    }
                }
            }

            /**
             * {@inheritdoc}
             */
            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                return $this->runner
                    ->runLoader(
                        $this->loader,
                        $this->factory->createRequest($input),
                        $this->factory->createResponse($output)
                    )
                    ->getCode();
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name)
    {
        return in_array($this->parse($name), $this->getNames());
    }

    private array $commandNames = [];

    /**
     * @throws NotFoundException
     * @throws LogicException
     */
    private function parse(string $name): string
    {
        if (!isset($this->commandNames[$name])) {
            [, $handler] = $this->route
                ->withRule($this->config->getRouteRule())
                ->match('/' . $name);

            [, $class, $actionId] = $this->controllerLoader->parseHandler($handler);

            $this->commandNames[$name] = $this->getCommandName(
                preg_replace('~' . str_replace('\\', '/', $this->config->rootNamespace) . '/~', '', str_replace('\\', '/', $class), 1),
                Str::camelToId($actionId, '-'),
            );
        }
        return $this->commandNames[$name];
    }

    private ?array $commands = null;

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        if ($this->commands === null) {
            $this->commands = $this->getCommands($this->getCommandFiles());
        }
        return $this->commands;
    }

    private function getCommandFiles(): array
    {
        return $this->getFiles(str_replace('\\', '/', $this->util->getAppPath()), $this->config->commandSuffix);
    }

    private function getFiles(string $directory, string $suffix): array
    {
        return array_map(static function ($filePath) use ($directory): string {
            return trim(str_replace([$directory, '.php'], '', $filePath), '/');
        }, FileHelper::findFiles($directory, [
            'filter' => (new PathMatcher())->only('**' . $suffix . '/*' . $suffix . '.php')
        ]));
    }

    private function getCommands(array $files): array
    {
        foreach ($files as $subClassName) {
            $map[$subClassName] = array_filter(
                (new ReflectionClass($this->config->rootNamespace . '\\' . str_replace('/', '\\', $subClassName)))->getMethods(ReflectionMethod::IS_PUBLIC),
                fn (ReflectionMethod $ref): bool => strpos($ref->getName(), $this->config->actionSuffix) !== false
            );
        }
        $commands = [];
        foreach ($map as $subClassName => $actions) {
            foreach ($actions as $ref) {
                $commands[] = $this->getCommandName(
                    $subClassName,
                    Str::camelToId(Str::rtrim($ref->getName(), $this->config->actionSuffix), '-', true)
                );
            }
        }
        return $commands;
    }

    private function getCommandName(string $subClassName, string $action): string
    {
        if ($action === $this->config->defaultAction) {
            $action = '';
        } else {
            $action = '/' . $action;
        }
        $prefix = trim(Str::camelToId(Str::rtrim('/' . $subClassName, '/' . $this->config->commandSuffix, false), '-', true), '/');
        $basename = Str::camelToId(basename($subClassName, $this->config->commandSuffix), '-', true);
        if ($prefix) {
            return sprintf('%s/%s%s', $prefix, $basename, $action);
        } else {
            return $basename . $action;
        }
    }
}
