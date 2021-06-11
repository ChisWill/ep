<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep\Annotation\Service;
use Ep\Contract\AspectInterface;
use Ep\Contract\HandlerInterface;
use Ep\Web\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class EchoIntAspect implements AspectInterface
{
    /**
     * @Service
     */
    private ServerRequest $request;

    public function process(HandlerInterface $handler)
    {
        $a = $this->request->getQueryParams()['a'] ?? 'none';

        /** @var ResponseInterface */
        $response = $handler->handle();
        $response->getBody()->write('get:' . $a . ', who:int<br>');
        return $response;
    }
}
