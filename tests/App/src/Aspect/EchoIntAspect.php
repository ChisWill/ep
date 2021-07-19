<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep\Contract\AspectInterface;
use Ep\Contract\HandlerInterface;
use Ep\Web\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class EchoIntAspect implements AspectInterface
{
    public function process(HandlerInterface $handler)
    {
        /** @var ResponseInterface */
        $response = $handler->handle();
        $response->getBody()->write(sprintf('echo int: %d<br>', mt_rand(10, 20)));
        return $response;
    }
}
