<?php

declare(strict_types=1);

namespace Ep\Tests\App\Component;

use HttpSoft\Message\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthFailHandler implements RequestHandlerInterface
{
    private ResponseFactory $factory;

    public function __construct(ResponseFactory $factory)
    {
        $this->factory = $factory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $res = $this->factory->createResponse(200);
        $res->getBody()->write('auth failed');
        return $res;
    }
}
