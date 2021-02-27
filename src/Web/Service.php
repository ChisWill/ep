<?php

declare(strict_types=1);

namespace Ep\Web;

use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final class Service
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function string(string $data = '', int $statusCode = Status::OK): ResponseInterface
    {
        $response = $this->responseFactory
            ->createResponse($statusCode)
            ->withHeader(Header::CONTENT_TYPE, 'text/html; charset=UTF-8');
        $response->getBody()->write($data);
        return $response;
    }

    /**
     * @param mixed $data
     */
    public function json($data = []): ResponseInterface
    {
        $response = $this->responseFactory
            ->createResponse(Status::OK)
            ->withHeader(Header::CONTENT_TYPE, 'application/json; charset=UTF-8');
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    public function redirect(string $url, int $statusCode = Status::FOUND): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse($statusCode)
            ->withHeader(Header::LOCATION, $url);
    }

    /**
     * @param mixed $result
     */
    public function toResponse($result): ResponseInterface
    {
        if ($result instanceof ResponseInterface) {
            return $result;
        } elseif (is_array($result)) {
            return $this->json($result);
        } elseif (is_scalar($result)) {
            return $this->string((string) $result);
        } else {
            return $this->string();
        }
    }
}
