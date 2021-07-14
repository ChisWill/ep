<?php

declare(strict_types=1);

namespace Ep\Console;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

class EventDispatcher implements SymfonyEventDispatcherInterface
{
    private PsrEventDispatcherInterface $dispatcher;

    public function __construct(PsrEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event, string $eventName = null): object
    {
        return $this->dispatcher->dispatch($event);
    }
}
