<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Object\Animal;

use Ep\Annotation\Inject;
use Ep\Tests\Support\Object\Engine\EngineInterface;

final class MegaBird extends Bird
{
    /**
     * @Inject
     */
    private EngineInterface $engine;

    public function getName(): string
    {
        return 'Wally Guge';
    }

    public function getSize(): int
    {
        return 100;
    }

    public function getPower(): int
    {
        return $this->engine->getPower();
    }
}
