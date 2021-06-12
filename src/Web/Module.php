<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep;
use Ep\Contract\FilterTrait;
use Ep\Contract\ModuleInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Module implements ModuleInterface
{
    use FilterTrait;

    /**
     * @return true|ResponseInterface
     */
    public function before(ServerRequestInterface $request)
    {
        return true;
    }

    public function after(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
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
