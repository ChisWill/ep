<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep\Annotation\Service;
use Ep\Contract\AspectInterface;
use Ep\Contract\HandlerInterface;
use Ep\Web\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class EchoStringAspect implements AspectInterface
{
    /**
     * @Service
     */
    private ServerRequest $request;

    public function process(HandlerInterface $handler)
    {
        $b = $this->request->getQueryParams()['b'] ?? 'none';

        /** @var ResponseInterface */
        $response = $handler->handle();
        $response->getBody()->write('get:' . $b . ', who:string<br>');
        return $response;
    }
}
