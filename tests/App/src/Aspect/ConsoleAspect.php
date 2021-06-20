<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep\Annotation\Inject;
use Ep\Contract\AspectInterface;
use Ep\Contract\ConsoleRequestInterface;
use Ep\Contract\ConsoleResponseInterface;
use Ep\Contract\HandlerInterface;

class ConsoleAspect implements AspectInterface
{
    /**
     * @Inject
     */
    private ConsoleRequestInterface $request;

    /**
     * @Inject
     */
    private ConsoleResponseInterface $response;

    public function process(HandlerInterface $handler)
    {
        $this->response->writeln('aspect start');
        /** @var ConsoleResponseInterface */
        $response = $handler->handle();
        $response->writeln('aspect after');
        return $response;
    }
}
