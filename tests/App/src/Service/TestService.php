<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep\Annotation\Inject;
use Ep\Web\Service;
use Psr\Http\Message\ResponseInterface;

final class TestService
{
    /**
     * @Inject
     */
    private Service $service;

    public function getRandom(): ResponseInterface
    {
        return $this->service->string((string) mt_rand(10, 100) . '<br>');
    }
}
