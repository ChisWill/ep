<?php

declare(strict_types=1);

namespace Ep\Console;

use Ep;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Ep\Contract\FilterTrait;
use Ep\Contract\ModuleInterface;

abstract class Module implements ModuleInterface
{
    use FilterTrait;

    /**
     * @return true|ConsoleResponseInterface
     */
    public function before(ConsoleRequestInterface $request)
    {
        return true;
    }

    public function after(ConsoleRequestInterface $request, ConsoleResponseInterface $response): ConsoleResponseInterface
    {
        return $response;
    }

    private ?Service $service = null;

    protected function getService(): Service
    {
        if ($this->service === null) {
            $this->service = Ep::getDi()->get(Service::class);
        }
        return $this->service;
    }
}
