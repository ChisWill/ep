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
    public function process(HandlerInterface $handler)
    {
        /** @var ConsoleResponseInterface */
        $response = $handler->handle();
        $response->writeln('aspect after');
        return $response;
    }
}
