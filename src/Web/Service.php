<?php

declare(strict_types=1);

namespace Ep\Web;

use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Service
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function string(string $data): ResponseInterface
    {
        $response = $this->responseFactory
            ->createResponse(Status::OK)
            ->withHeader(Header::CONTENT_TYPE, 'text/html; charset=UTF-8');
        $response->getBody()->write($data);
        return $response;
    }

    public function json(array $data): ResponseInterface
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
}
