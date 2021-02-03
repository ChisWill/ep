<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Standard\ResponseHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class Controller extends \Ep\Base\Controller
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    protected function createResponseHandler(): ResponseHandlerInterface
    {
        return new View($this, '');
    }

    protected function beforeAction(): bool
    {
        return true;
    }

    protected function afterAction(?ResponseHandlerInterface $responseHandler): void
    {
    }

    protected function jsonSuccess()
    {
    }

    protected function jsonError()
    {
    }

    protected function redirect(string $url, $statusCode = 302)
    {
        $response = $this->responseFactory->createResponse($statusCode)->withHeader('Location', $url);
        $rr = $response->getBody()->write("abc");
        $r = $response->getBody()->getContents();
        tes($rr, $r);
        return null;
    }
}
