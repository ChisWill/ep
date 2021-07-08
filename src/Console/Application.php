<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Base\ErrorHandler;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\InjectorInterface;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Application extends SymfonyApplication
{
    private InjectorInterface $injector;
    private InputInterface $input;
    private OutputInterface $output;
    private Factory $factory;
    private ErrorHandler $errorHandler;
    private ErrorRenderer $errorRenderer;

    public function __construct(
        InjectorInterface $injector,
        InputInterface $input,
        OutputInterface $output,
        Factory $factory,
        ErrorHandler $errorHandler,
        ErrorRenderer $errorRenderer
    ) {
        $this->injector = $injector;
        $this->input = $input;
        $this->output = $output;
        $this->factory = $factory;
        $this->errorHandler = $errorHandler;
        $this->errorRenderer = $errorRenderer;

        parent::__construct('Ep', Ep::VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function run(?InputInterface $input = null, ?OutputInterface $output = null)
    {
        $input ??= $this->input;
        $output ??= $this->output;

        $request = $this->createRequest($input);

        $this->registerEvent($request);

        $this->setCommandLoader($this->injector->make(CommandLoader::class, compact('request')));

        return parent::run($input ?? $this->input, $output ?? $this->output);
    }

    private ?ConsoleRequestInterface $request = null;

    public function withRequest(ConsoleRequestInterface $request): self
    {
        $new = clone $this;
        $new->request = $request;
        return $new;
    }

    private function createRequest(InputInterface $input = null): ConsoleRequestInterface
    {
        return $this->request ?? $this->factory->createRequest($input);
    }

    private function registerEvent(ConsoleRequestInterface $request): void
    {
        $this->errorHandler
            ->configure([
                'errorRenderer' => $this->errorRenderer
            ])
            ->register($request);
    }

    /**
     * {@inheritDoc}
     */
    public function extractNamespace(string $name, int $limit = null)
    {
        $parts = explode('/', $name, -1);

        return ucfirst(implode('/', null === $limit ? $parts : array_slice($parts, 0, $limit)));
    }
}
