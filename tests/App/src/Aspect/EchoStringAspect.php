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

    private string $name;
    private int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function process(HandlerInterface $handler)
    {
        $b = $this->request->getQueryParams()['b'] ?? 'none';

        /** @var ResponseInterface */
        $response = $handler->handle();
        $response->getBody()->write('get:' . $b . ', who:string,params:' . json_encode([$this->name, $this->age]) . '<br>');
        return $response;
    }
}
