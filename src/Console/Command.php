<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Contract\ConfigurableTrait;
use Ep\Contract\ContextTrait;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Ep\Contract\ControllerInterface;
use Ep\Contract\FilterTrait;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use LogicException;

abstract class Command implements ControllerInterface
{
    use ContextTrait, FilterTrait, ConfigurableTrait;

    /**
     * {@inheritDoc}
     */
    public string $id;
    /**
     * {@inheritDoc}
     */
    public string $actionId;

    /**
     * @return true|ConsoleResponseInterface
     */
    public function before(ConsoleRequestInterface $request, ConsoleResponseInterface $response)
    {
        return true;
    }

    public function after(ConsoleRequestInterface $request, ConsoleResponseInterface $response): ConsoleResponseInterface
    {
        return $response;
    }

    private array $definitions = [];

    /**
     * @return CommandDefinition[]
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    protected function createDefinition(string $actionId): CommandDefinition
    {
        $this->definitions[$actionId] ??= new CommandDefinition();

        return $this->definitions[$actionId];
    }

    private ?Service $service = null;

    protected function getService(): Service
    {
        if ($this->service === null) {
            $this->service = Ep::getDi()->get(Service::class);
        }
        return $this->service;
    }

    protected function success(string $message = ''): ConsoleResponseInterface
    {
        if ($message) {
            $this->getService()->writeln($message);
        }
        return $this->getService()->status(SymfonyCommand::SUCCESS);
    }

    protected function error(string $message = ''): ConsoleResponseInterface
    {
        if ($message) {
            $this->getService()->writeln($message);
        }
        return $this->getService()->status(SymfonyCommand::FAILURE);
    }

    protected function write(string $message = '', int $options = 0): void
    {
        $this->getService()->write($message, $options);
    }

    protected function writeln(string $message = '', int $options = 0): void
    {
        $this->getService()->writeln($message, $options);
    }

    protected function confirm(string $message, bool $default = false): bool
    {
        return $this->getService()->confirm($message, $default);
    }

    public function setMiddlewares(array $middlewares): void
    {
        throw new LogicException('Command doesn\'t have middlewares yet.');
    }

    public function getMiddlewares(): array
    {
        throw new LogicException('Command doesn\'t have middlewares yet.');
    }
}
