<?php

declare(strict_types=1);

namespace Ep\Contract;

interface FactoryInterface
{
    /**
     * @param  string|array $definition
     * 
     * @return mixed
     */
    public function create($definition);

    public function setDefinitions(array $definitions): void;
}
