<?php

declare(strict_types=1);

namespace Ep\Console;

use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

final class CommandLoader implements CommandLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(string $name)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name)
    {
        return in_array($name, $this->getNames());
    }

    /**
     * {@inheritdoc}
     */
    public function getNames()
    {
        return [];
    }
}
