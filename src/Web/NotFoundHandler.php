<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Contract\ContextTrait;
use Ep\Contract\ContextInterface;
use Ep\Contract\NotFoundHandlerInterface;
use Yiisoft\Http\Status;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class NotFoundHandler implements NotFoundHandlerInterface, ContextInterface
{
    use ContextTrait;

    /**
     * {@inheritDoc}
     */
    public string $id = 'error';

    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->service->string(
            $this->getView()->renderPartial('notFound', [
                'path' => $request->getUri()->getPath(),
                'exception' => $request->getAttribute('exception')
            ]),
            Status::NOT_FOUND
        );
    }

    protected function getViewPath(): string
    {
        return '@ep/views';
    }
}
