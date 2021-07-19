<?php

declare(strict_types=1);

namespace Ep\Tests\App\Aspect;

use Ep\Contract\AspectInterface;
use Ep\Contract\HandlerInterface;
use Ep\Helper\Str;
use Psr\Http\Message\ResponseInterface;

class EchoStringAspect implements AspectInterface
{
    private string $name;
    private int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function process(HandlerInterface $handler)
    {
        /** @var ResponseInterface */
        $response = $handler->handle();
        $response->getBody()->write(sprintf('echo string: %s, params: %s<br>', Str::random(6), json_encode([$this->name, $this->age])));
        return $response;
    }
}
