<?php

declare(strict_types=1);

namespace Ep\Tests\App\Service;

use Ep\Annotation\Inject;
use Ep\Web\Service;
use Psr\Http\Message\ResponseInterface;

final class DemoService
{
    /**
     * @Inject
     */
    private TestService $testService;

    public function getAttr(): array
    {
        return $this->testService->getAttr();
    }
}
