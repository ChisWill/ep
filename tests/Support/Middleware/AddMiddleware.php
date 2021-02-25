<?php

declare(strict_types=1);

namespace Ep\Tests\Support\Middleware;

use Ep\Web\Service;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddMiddleware implements MiddlewareInterface
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withQueryParams([
            'name' => 'Mary',
            'age' => 18
        ]);
        $response = $handler->handle($request);
        $body = $response->getBody();
        $body->rewind();
        $content = $body->getContents();
        $json = json_decode($content, true);
        $json['add'] = self::class;
        return $this->service->json($json);
    }
}
