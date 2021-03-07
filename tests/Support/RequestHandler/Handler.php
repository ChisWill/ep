<?php

declare(strict_types=1);

namespace Ep\Tests\Support\RequestHandler;

use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Handler
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function do(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $age = $request->getAttribute('age', 0);
        $request = $request->withAttribute('job' . $age, "I am a soldier for {$age} years old.");
        return $handler->handle($request);
    }
}
