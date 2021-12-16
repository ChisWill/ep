<?php

declare(strict_types=1);

namespace Ep\Base;

use Ep\Contract\FactoryInterface;
use Yiisoft\Factory\Factory as YiiFactory;

final class Factory implements FactoryInterface
{
    private YiiFactory $factory;

    public function __construct(YiiFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function create($definition)
    {
        return $this->factory->create($definition);
    }

    /**
     * {@inheritDoc}
     */
    public function setDefinitions(array $definitions): void
    {
        $this->factory = $this->factory->withDefinitions($definitions);
    }
}
