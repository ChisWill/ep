<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Helper\Date;
use Yiisoft\Http\ContentDispositionHeader;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use SplFileInfo;

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
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response;
    }

    public function redirect(string $url, int $statusCode = Status::FOUND): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse($statusCode)
            ->withHeader(Header::LOCATION, $url);
    }

    /**
     * @param SplFileInfo|string $file
     */
    public function download($file, string $name = null): ResponseInterface
    {
        if (is_string($file)) {
            $file = new SplFileInfo($file);
        }

        $response = $this->responseFactory
            ->createResponse(Status::OK)
            ->withHeader(Header::CACHE_CONTROL, 'no-cache')
            ->withHeader(Header::ETAG, $this->getEtagValue(hash_file('sha256', $file->getPathname(), true)))
            ->withHeader(Header::LAST_MODIFIED, Date::toGMT($file->getMTime()))
            ->withHeader(Header::CONTENT_DISPOSITION, ContentDispositionHeader::attachment($name ?: $file->getFilename()));

        $reader = $file->openFile('r');
        $body = $response->getBody();
        while (!$reader->eof()) {
            $body->write($reader->fgets());
        }

        return $response;
    }

    public function status(int $statusCode = Status::OK): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse($statusCode);
    }

    private function getEtagValue(string $etag, bool $weak = false): string
    {
        return ($weak === true ? 'W/' : '') . '"' . base64_encode($etag) . '"';
    }
}
