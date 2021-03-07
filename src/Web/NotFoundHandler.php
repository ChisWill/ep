<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Base\ContextTrait;
use Ep\Contract\ContextInterface;
use Ep\Contract\NotFoundHandlerInterface;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class NotFoundHandler implements NotFoundHandlerInterface, ContextInterface
{
    use ContextTrait;

    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function __get($name)
    {
        if ($name === 'id') {
            return 'error';
        }
        throw new InvalidArgumentException("The \"{$name}\" property is not exists.");
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->service->status(Status::NOT_FOUND);
        $response->getBody()->write(
            $this->getView()->renderPartial('notFound', [
                'path' => $request->getUri()->getPath(),
                'exception' => $request->getAttribute('exception')
            ])
        );
        return $response;
    }

    public function getViewPath(): string
    {
        return '@ep/views';
    }
}
