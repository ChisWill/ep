<?php

declare(strict_types=1);

namespace Ep\Swoole\Http;

use Swoole\Http\Response;
use Yiisoft\Http\Status;
use Psr\Http\Message\ResponseInterface;

final class Emitter
{
    private const NO_BODY_RESPONSE_CODES = [
        Status::CONTINUE,
        Status::SWITCHING_PROTOCOLS,
        Status::PROCESSING,
        Status::NO_CONTENT,
        Status::RESET_CONTENT,
        Status::NOT_MODIFIED,
    ];

    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function emit(ResponseInterface $response, bool $withoutBody = false): void
    {
        $status = $response->getStatusCode();
        $withoutBody = $withoutBody || !$this->shouldOutputBody($response);
        $withoutContentLength = $withoutBody || $response->hasHeader('Transfer-Encoding');
        if ($withoutContentLength) {
            $response = $response->withoutHeader('Content-Length');
        }

        $this->response->setStatusCode($status);

        foreach ($response->getHeaders() as $header => $values) {
            $this->response->setHeader($header, implode(';', $values));
        }

        if (!$withoutBody) {
            if (!$withoutContentLength && !$response->hasHeader('Content-Length')) {
                $contentLength = $response->getBody()->getSize();
                if ($contentLength !== null) {
                    $this->response->setHeader('Content-Length', (string) $contentLength);
                }
            }

            $this->emitBody($response);
        }
    }

    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }
        while (!$body->eof()) {
            $this->response->write($body->read(1048576));
        }
        $this->response->end();
    }

    private function shouldOutputBody(ResponseInterface $response): bool
    {
        if (in_array($response->getStatusCode(), self::NO_BODY_RESPONSE_CODES, true)) {
            return false;
        }
        $body = $response->getBody();
        if (!$body->isReadable()) {
            return false;
        }
        $size = $body->getSize();
        if ($size !== null) {
            return $size > 0;
        }
        if ($body->isSeekable()) {
            $body->rewind();
            $byte = $body->read(1);
            if ($byte === '' || $body->eof()) {
                return false;
            }
        }
        return true;
    }
}
