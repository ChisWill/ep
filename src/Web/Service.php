<?php

declare(strict_types=1);

namespace Ep\Web;

use Ep\Helper\Date;
use Yiisoft\Http\ContentDispositionHeader;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use JsonException;
use SplFileInfo;

final class Service
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    private ?ServerRequestInterface $request = null;

    public function withRequest(ServerRequestInterface $request): self
    {
        $new = clone $this;
        $new->request = $request;
        return $new;
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
     * 
     * @throws JsonException
     */
    public function json($data = []): ResponseInterface
    {
        $response = $this->responseFactory
            ->createResponse(Status::OK)
            ->withHeader(Header::CONTENT_TYPE, 'application/json; charset=UTF-8');
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
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

        $etag = $this->getEtagValue(hash_file('sha256', $file->getPathname(), true));
        $lastModified = Date::toGMT($file->getMTime());

        if ($this->compareHeaders([
            Header::IF_NONE_MATCH => $etag,
            Header::IF_MODIFIED_SINCE => $lastModified
        ])) {
            return $this->status(Status::NOT_MODIFIED);
        }

        $response = $this->responseFactory
            ->createResponse(Status::OK)
            ->withHeader(Header::CACHE_CONTROL, 'no-cache')
            ->withHeader(Header::ETAG, $etag)
            ->withHeader(Header::LAST_MODIFIED, $lastModified)
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
        return $this->responseFactory->createResponse($statusCode);
    }

    private function compareHeaders(array $pairs): bool
    {
        if (!$this->request) {
            return false;
        }

        foreach ($pairs as $name => $value) {
            if (!in_array($value, $this->request->getHeader($name))) {
                return false;
            }
        }

        return true;
    }

    private function getEtagValue(string $etag, bool $weak = false): string
    {
        return ($weak === true ? 'W/' : '') . '"' . base64_encode($etag) . '"';
    }
}
